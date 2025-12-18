<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>IA Maze Solver - Q-Learning</title>
    <style>
        body { font-family: sans-serif; display: flex; flex-direction: column; align-items: center; background: #2c3e50; color: white; }
        #maze-container { display: grid; gap: 2px; margin: 20px; background: #7f8c8d; border: 5px solid #7f8c8d; }
        .cell { width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; font-weight: bold; }
        .wall { background: #34495e; }
        .path { background: #ecf0f1; }
        .agent { background: #e74c3c; border-radius: 50%; color: white; }
        .goal { background: #2ecc71; color: white; }
        .controls { background: #34495e; padding: 20px; border-radius: 8px; }
        button { cursor: pointer; padding: 8px 15px; }
    </style>
</head>
<body>

    <h1>IA Maze Solver (Q-Learning)</h1>

    <div id="maze-container"></div>

    <div class="controls">
        <label>Épisodes: <input type="number" id="episodes" value="30"></label>
        <label>Epsilon: <input type="number" id="epsilon" step="0.1" value="0.1"></label>
        <button onclick="startTraining()">Lancer l'entraînement</button>
        <button onclick="saveQTable()">Sauvegarder la Q-Table</button>
        <p id="stats">En attente...</p>
    </div>

    <script>
        // --- CONFIGURATION ---
        const MAZE_LAYOUT = [
            "A110001000", "0100101000", "0000100000", "0111111110", "0100000000",
            "1101011111", "0000000000", "1111111101", "1000100000", "G010001000"
        ];
        const SIZE = 10;
        let qTable = {};
        let agentPos = {x: 0, y: 0};
        let goalPos = {x: 0, y: 9};

        // --- INITIALISATION ---
        function initMaze() {
            const container = document.getElementById('maze-container');
            container.style.gridTemplateColumns = `repeat(${SIZE}, 30px)`;
            container.innerHTML = '';
            for (let y = 0; y < SIZE; y++) {
                for (let x = 0; x < SIZE; x++) {
                    const cell = document.createElement('div');
                    cell.id = `cell-${x}-${y}`;
                    cell.className = 'cell ' + (MAZE_LAYOUT[y][x] === '1' ? 'wall' : 'path');
                    if (MAZE_LAYOUT[y][x] === 'A') { cell.classList.add('agent'); cell.innerText = 'A'; agentPos = {x, y}; }
                    if (MAZE_LAYOUT[y][x] === 'G') { cell.classList.add('goal'); cell.innerText = 'G'; goalPos = {x, y}; }
                    container.appendChild(cell);
                }
            }
        }

        // --- LOGIQUE AGENT (JS version de agent.py) ---
        function getAction(state, epsilon) {
            if (Math.random() < epsilon || !qTable[state]) {
                return Math.floor(Math.random() * 4);
            }
            return qTable[state].indexOf(Math.max(...qTable[state]));
        }

        async function startTraining() {
            const episodes = parseInt(document.getElementById('episodes').value);
            let epsilon = parseFloat(document.getElementById('epsilon').value);
            
            for (let ep = 0; ep < episodes; ep++) {
                let state = resetEnv();
                let done = false;
                let steps = 0;

                while (!done && steps < 200) {
                    let action = getAction(state, epsilon);
                    let result = step(action);
                    
                    // Apprentissage (Q-Learning)
                    if (!qTable[state]) qTable[state] = [0, 0, 0, 0];
                    if (!qTable[result.nextState]) qTable[result.nextState] = [0, 0, 0, 0];

                    let qPredict = qTable[state][action];
                    let qTarget = result.reward + 0.95 * Math.max(...qTable[result.nextState]);
                    qTable[state][action] += 0.1 * (qTarget - qPredict);

                    state = result.nextState;
                    done = result.done;
                    steps++;

                    if (ep % 5 === 0) { // Rendu visuel
                        updateUI();
                        await new Promise(r => setTimeout(r, 20));
                    }
                }
                document.getElementById('stats').innerText = `Épisode ${ep+1}/${episodes} terminé en ${steps} étapes.`;
            }
        }

        function step(action) {
            let nextX = agentPos.x, nextY = agentPos.y;
            if (action === 0 && agentPos.y > 0) nextY--;      // Haut
            else if (action === 1 && agentPos.y < SIZE-1) nextY++; // Bas
            else if (action === 2 && agentPos.x > 0) nextX--; // Gauche
            else if (action === 3 && agentPos.x < SIZE-1) nextX++; // Droite

            let reward = -0.05;
            let done = false;

            if (MAZE_LAYOUT[nextY][nextX] === '1') {
                reward = -0.5; // Mur
            } else {
                agentPos = {x: nextX, y: nextY};
                if (agentPos.x === goalPos.x && agentPos.y === goalPos.y) {
                    reward = 10;
                    done = true;
                }
            }
            return { nextState: `${agentPos.x},${agentPos.y}`, reward, done };
        }

        function resetEnv() {
            agentPos = {x: 0, y: 0}; // Ajuster selon le labyrinthe
            return `${agentPos.x},${agentPos.y}`;
        }

        function updateUI() {
            document.querySelectorAll('.agent').forEach(el => {
                el.classList.remove('agent');
                el.innerText = '';
            });
            const currentCell = document.getElementById(`cell-${agentPos.x}-${agentPos.y}`);
            currentCell.classList.add('agent');
            currentCell.innerText = 'A';
        }

        // --- SAUVEGARDE VERS PHP ---
        function saveQTable() {
            fetch('save.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(qTable)
            }).then(response => response.text())
              .then(data => alert("Sauvegardé sur le serveur !"));
        }

        // Charger au démarrage
        fetch('q_table.json').then(r => r.json()).then(data => { qTable = data; }).catch(e => console.log("Nouvelle table"));
        initMaze();
    </script>
</body>
</html>