<?php
include '../includes/header.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crash Game Visualizer</title>
    <link rel="stylesheet" href="/crash/assets/css/styles.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
            overflow: hidden; /* Hide overflow to ensure shooting stars stay within view */
        }
        main {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        h2 {
            text-align: center;
        }
        #chart-container {
            width: 100%;
            height: 250px;
            position: relative;
        }
        #result {
            margin-top: 20px;
            text-align: center;
        }
        .bet-inputs {
            margin: 20px 0;
        }
        .bet-inputs label {
            display: block;
            margin-bottom: 5px;
        }
        .bet-inputs input {
            width: 100%;
            padding: 10px;
            margin-bottom: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .bet-inputs button {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            background-color: #28a745;
            color: #fff;
            cursor: pointer;
        }
        .bet-inputs button:hover {
            background-color: #218838;
        }
        .win {
            color: green;
        }
        .loss {
            color: red;
        }
        /* Shooting Stars Animation Styles */
#shooting-stars {
    position: absolute;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    overflow: hidden;
    pointer-events: none;
    display: none; /* Hidden by default */
    z-index: 9999; /* Ensure it appears on top */
}

.star {
    position: absolute;
    width: 2px;
    height: 2px;
    background: white;
    border-radius: 50%;
    animation: shoot 4s linear;
}

@keyframes shoot {
    0% {
        transform: translateY(-100vh) translateX(-100vw) scale(0.5);
        opacity: 0;
    }
    50% {
        transform: translateY(50vh) translateX(50vw) scale(1);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) translateX(100vw) scale(1);
        opacity: 0;
    }
}

    </style>
</head>
<body>

    <main>
        <h2>Crash Game Visualizer</h2>
        <!-- Container for shooting stars animation -->
<div id="shooting-stars"></div>

        <div id="result"></div>
        <div id="chart-container">
            <canvas id="multiplier-chart"></canvas>
            <div class="shooting-stars" id="shooting-stars"></div>
        </div>

        <div class="bet-inputs">
            <form id="bet-form">
                <label for="bet_amount">Bet Amount (USD):</label>
                <input type="number" id="bet_amount" name="bet_amount" step="0.01" min="0.01" required><br>
                <label for="predicted_multiplier">Predicted Multiplier:</label>
                <input type="number" id="predicted_multiplier" name="predicted_multiplier" step="0.01" min="1.00" required><br>
                <button type="submit">Place Bet</button>
            </form>
        </div>

        <div id="recent-bets">
            <h3>Recent Bets</h3>
            <table>
                <thead>
                    <tr>
                        <th>Bet Amount (USD)</th>
                        <th>Predicted Multiplier</th>
                        <th>Actual Multiplier</th>
                        <th>Result</th>
                    </tr>
                </thead>
                <tbody id="recent-bets-body">
                    <!-- Recent bets will be inserted here -->
                </tbody>
            </table>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    var ctx = document.getElementById('multiplier-chart').getContext('2d');
    var dataLimit = 50; // Number of points to show on the chart

    // Initialize the chart
    var multiplierChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: [],
            datasets: [{
                label: 'Multiplier',
                data: [{x: 0, y: 0}], // Start with 0,0
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                fill: true,
                lineTension: 0.3, // Smooth curve
                pointRadius: 0 // Remove data points
            }]
        },
        options: {
            scales: {
                x: {
                    type: 'linear',
                    position: 'bottom',
                    title: {
                        display: true,
                        text: 'Time'
                    },
                    ticks: {
                        maxRotation: 0
                    }
                },
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'Multiplier'
                    }
                }
            },
            animation: {
                duration: 0 // Disable animation for real-time updates
            }
        }
    });

    function animateToMultiplier(crashPoint, callback) {
        // Clear previous animation
        multiplierChart.data.labels = [];
        multiplierChart.data.datasets[0].data = [{x: 0, y: 0}];
        
        var time = 0;
        var interval = 50; // milliseconds between updates
        var maxTime = 3000; // total animation time in milliseconds
        var increment = crashPoint / (maxTime / interval);

        function update() {
            if (time <= maxTime) {
                var currentMultiplier = Math.min(increment * (time / interval), crashPoint);
                updateChart(currentMultiplier);
                time += interval;
                requestAnimationFrame(update);
            } else {
                updateChart(crashPoint);
                if (callback) {
                    callback(); // Execute the callback after animation is complete
                }
            }
        }

        requestAnimationFrame(update);
    }

    function updateChart(multiplier) {
        // Add new data
        var timeLabel = (multiplierChart.data.labels.length > 0 ? multiplierChart.data.labels[multiplierChart.data.labels.length - 1] + 1 : 1);
        multiplierChart.data.labels.push(timeLabel);
        multiplierChart.data.datasets[0].data.push({ x: timeLabel, y: multiplier });

        // Remove old data to keep the chart scrolling
        if (multiplierChart.data.labels.length > dataLimit) {
            multiplierChart.data.labels.shift();
            multiplierChart.data.datasets[0].data.shift();
        }

        multiplierChart.update();
    }

    function updateRecentBets() {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '../includes/get_recent_bets.php', true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var betsBody = document.getElementById('recent-bets-body');
                betsBody.innerHTML = '';

                if (response.bets && response.bets.length > 0) {
                    response.bets.forEach(function(bet) {
                        var resultClass = bet.result === 'Win' ? 'win' : 'loss';
                        betsBody.innerHTML += `
                            <tr>
                                <td>${parseFloat(bet.bet_amount).toFixed(2)} USD</td>
                                <td>${parseFloat(bet.predicted_multiplier).toFixed(2)}</td>
                                <td>${parseFloat(bet.actual_multiplier).toFixed(2)}</td>
                                <td class="${resultClass}">${bet.result}</td>
                            </tr>
                        `;
                    });
                } else {
                    betsBody.innerHTML = '<tr><td colspan="4">No recent bets found.</td></tr>';
                }
            }
        };
        xhr.send();
    }

    function createShootingStars() {
        var container = document.getElementById('shooting-stars');
        container.innerHTML = ''; // Clear previous stars

        var numStars = 10;
        for (var i = 0; i < numStars; i++) {
            var star = document.createElement('div');
            star.className = 'star';
            star.style.left = Math.random() * 100 + 'vw';
            star.style.top = Math.random() * 100 + 'vh';
            star.style.animationDuration = Math.random() * 2 + 2 + 's'; // Random duration between 2s and 4s
            star.style.animationDelay = Math.random() * 2 + 's'; // Random delay
            container.appendChild(star);
        }
    }

    function createShootingStars() {
    var container = document.getElementById('shooting-stars');
    container.innerHTML = ''; // Clear previous stars

    var numStars = 10;
    for (var i = 0; i < numStars; i++) {
        var star = document.createElement('div');
        star.className = 'star';
        star.style.left = Math.random() * 100 + 'vw';
        star.style.top = Math.random() * 100 + 'vh';
        star.style.animationDuration = Math.random() * 2 + 2 + 's'; // Random duration between 2s and 4s
        star.style.animationDelay = Math.random() * 2 + 's'; // Random delay
        container.appendChild(star);
    }
}

function showShootingStars() {
    var starsContainer = document.getElementById('shooting-stars');
    starsContainer.style.display = 'block'; // Show stars
    createShootingStars(); // Create stars

    // Hide the stars after animation
    setTimeout(function() {
        starsContainer.style.display = 'none';
    }, 4000); // Adjust time to match the longest animation duration
}

document.getElementById('bet-form').addEventListener('submit', function(e) {
    e.preventDefault();

    var betAmount = document.getElementById('bet_amount').value;
    var predictedMultiplier = document.getElementById('predicted_multiplier').value;

    // Clear the previous result message
    document.getElementById('result').innerHTML = '';

    var xhr = new XMLHttpRequest();
    xhr.open('POST', '../includes/bet.php', true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                var response = JSON.parse(xhr.responseText);
                var resultDiv = document.getElementById('result');

                if (response.success) {
                    var crashPoint = parseFloat(response.message.match(/Multiplier was ([0-9.]+)/)[1]);
                    animateToMultiplier(crashPoint, function() {
                        // Display the result message after animation is complete
                        resultDiv.innerHTML = '<p>' + response.message + '</p>';
                        if (response.message.includes("Congratulations")) {
                            showShootingStars(); // Trigger shooting stars animation on win
                        }
                    });
                    updateRecentBets(); // Update the recent bets table
                } else if (response.error) {
                    resultDiv.innerHTML = '<p style="color: red;">' + response.error + '</p>';
                }
            } else {
                console.error('Error:', xhr.statusText);
            }
        }
    };

    xhr.send('bet_amount=' + encodeURIComponent(betAmount) + '&predicted_multiplier=' + encodeURIComponent(predictedMultiplier));
});


    // Initial fetch of recent bets
    updateRecentBets();
</script>

</body>
</html>

