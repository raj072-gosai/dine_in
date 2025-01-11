<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=800, height=480, initial-scale=1.0">
    <title>Order Dashboard</title>
    <script>
        let currentCardIndex = 0;

        function fetchData() {
            fetch('fetch_data.php')
                .then(response => response.json())
                .then(data => {
                    updateCards(data);
                })
                .catch(error => console.error('Error fetching data:', error));
        }

        function updateCards(data) {
            const container = document.getElementById('card-container');
            container.innerHTML = '';

            for (let i = 1; i <= 8; i++) {
                const tableData = data.find(row => parseInt(row.table_number) === i);
                const card = document.createElement('div');
                card.className = `card ${i === currentCardIndex + 1 ? 'selected' : ''}`;
                card.innerHTML = `
                    <h2>Table ${i}</h2>
                    <ul>
                        ${tableData ? tableData.orders.split(', ').map(order => `<li>${order}</li>`).join('') : '<li>No orders for this table</li>'}
                    </ul>`;
                container.appendChild(card);

                card.addEventListener('click', () => {
                    currentCardIndex = i - 1;
                    document.querySelectorAll('.card').forEach(c => c.classList.remove('selected'));
                    card.classList.add('selected');
                });

                card.addEventListener('dblclick', () => {
                    if (tableData) {
                        const confirmDelete = confirm(`The order for Table ${i} is ready?`);
                        if (confirmDelete) {
                            fetch(`delete.php?table_number=${i}`)
                                .then(() => {
                                    currentCardIndex = 0;
                                    fetchData();
                                })
                                .catch(error => console.error('Error deleting card:', error));
                        }
                    }
                });
            }
        }

        fetchData();
        setInterval(fetchData, 5000);
    </script>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f3f4f6;
            margin: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: flex-start;
            height: 100vh;
            overflow: hidden;
            cursor: none;
        }

        .container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            padding: 0;
        }

        h1 {
            margin: 0;
            padding: 10px;
            font-size: 1.5rem;
            text-align: center;
            background: #007bff;
            color: #ffffff;
        }

        .card-container {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            padding: 10px;
            overflow-y: scroll;
            scrollbar-width: none;
            height: calc(100% - 50px);
        }

        .card-container::-webkit-scrollbar {
            display: none;
        }

        .card {
        background: #ffffff;
        border-radius: 10px;
        box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
        padding: 15px;
        text-align: center;
        font-size: 1rem;
        max-height: 200px; /* Set a maximum height for the card */
        overflow-y: auto; /* Enable vertical scrolling if content exceeds max height */
    }

    .card::-webkit-scrollbar {
        width: 6px; /* Customize scrollbar width */
    }

    .card::-webkit-scrollbar-thumb {
        background-color: #007bff; /* Customize scrollbar thumb color */
        border-radius: 5px; /* Rounded corners for scrollbar thumb */
    }

    .card::-webkit-scrollbar-track {
        background: #f3f4f6; /* Customize scrollbar track color */
    }

        .card.selected {
            border: 2px solid #007bff;
            transform: scale(1.05);
            box-shadow: 0px 6px 12px rgba(0, 123, 255, 0.5);
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0px 8px 16px rgba(0, 0, 0, 0.3);
        }

        .card h2 {
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .card ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .card ul li {
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <h1>Order Dashboard</h1>
        <div class="card-container" id="card-container">
            <!-- Cards will be dynamically populated here -->
        </div>
    </div>
</body>

</html>
