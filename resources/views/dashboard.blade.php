```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mallow Inventory | Dashboard</title>

    <style>
        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #243247;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            width: 240px;
            height: 100vh;
            background: #1e2d45;
            color: white;
            padding: 28px 18px;
        }

        .brand {
            font-size: 22px;
            font-weight: bold;
            margin: 0 0 8px;
        }

        .subtitle {
            color: #b9c5d6;
            font-size: 13px;
            margin: 0 0 35px;
        }

        .sidebar nav a {
            display: block;
            color: #d7e0ed;
            text-decoration: none;
            padding: 13px 15px;
            margin-bottom: 8px;
            border-radius: 8px;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: #334966;
            color: white;
        }

        .main {
            margin-left: 240px;
            padding: 32px;
        }

        .page-header {
            margin-bottom: 28px;
        }

        .page-header h1 {
            margin: 0 0 8px;
        }

        .page-header p {
            margin: 0;
            color: #64748b;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
        }

        .card {
            background: white;
            padding: 24px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000d;
        }

        .card h3 {
            margin-top: 0;
            color: #64748b;
            font-size: 15px;
        }

        .number {
            font-size: 32px;
            font-weight: bold;
        }

        @media (max-width: 700px) {
            .sidebar {
                position: relative;
                width: 100%;
                height: auto;
            }

            .sidebar nav {
                display: flex;
                flex-wrap: wrap;
                gap: 5px;
            }

            .sidebar nav a {
                margin-bottom: 0;
            }

            .main {
                margin-left: 0;
                padding: 22px;
            }
        }
    </style>
</head>

<body>
    <aside class="sidebar">
        <div class="brand">Mallow Inventory</div>
        <p class="subtitle">Store Management System</p>

        <nav>
            <a href="/dashboard" class="active">Dashboard</a>
            <a href="/inventory">Inventory</a>
            <a href="/orders">Orders</a>
            <a href="/customers">Customers</a>
        </nav>
    </aside>

    <main class="main">
        <header class="page-header">
            <h1>Dashboard Overview</h1>
            <p>Monitor your store's products and stock levels.</p>
        </header>

        <section class="cards">
            <div class="card">
                <h3>Total Products</h3>
                <div class="number" id="total-products">—</div>
            </div>

            <div class="card">
                <h3>Low Stock Products</h3>
                <div class="number" id="low-stock">—</div>
            </div>
        </section>
    </main>

    <script>
        async function loadDashboard() {
            try {
                const [productsResponse, lowStockResponse] =
                    await Promise.all([
                        fetch('/api/products'),
                        fetch('/api/products/low-stock?threshold=10')
                    ]);

                if (!productsResponse.ok || !lowStockResponse.ok) {
                    throw new Error('Unable to load dashboard statistics.');
                }

                const productsData = await productsResponse.json();
                const lowStockData = await lowStockResponse.json();

                document.getElementById('total-products').textContent =
                    (productsData.products || []).length;

                const lowStockProducts = Array.isArray(lowStockData)
                    ? lowStockData
                    : (lowStockData.products || []);

                document.getElementById('low-stock').textContent =
                    lowStockProducts.length;

            } catch (error) {
                console.error('Dashboard loading error:', error);
                document.getElementById('total-products').textContent = '—';
                document.getElementById('low-stock').textContent = '—';
            }
        }

        loadDashboard();
    </script>
</body>
</html>