document.addEventListener("DOMContentLoaded", function () {
    setInterval(function () {
        fetch('/crashgame/game/get_multiplier.php')
            .then(response => response.text())
            .then(multiplier => {
                document.getElementById("multiplier").innerText = multiplier + "x";
            });
    }, 1000);

    document.getElementById("betForm").addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        fetch('/crashgame/game/bet.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert("You won: " + data.cash_out);
            } else {
                alert(data.error);
            }
        });
    });
});

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('addBalanceForm');
    const responseMessage = document.getElementById('responseMessage');

    form.addEventListener('submit', (event) => {
        event.preventDefault(); // Prevent form from submitting the traditional way

        const formData = new FormData(form);

        fetch('add_balance.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                responseMessage.textContent = "Balance added successfully!";
                responseMessage.style.color = "green";
            } else {
                responseMessage.textContent = "Error: " + data.message;
                responseMessage.style.color = "red";
            }
        })
        .catch(error => {
            responseMessage.textContent = "An error occurred: " + error.message;
            responseMessage.style.color = "red";
        });
    });
});

