
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mallow | Product Inventory</title>

    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f5f7fb;
            color: #202938;
        }

        .layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 230px;
            flex-shrink: 0;
            background: #17243b;
            color: white;
            padding: 28px 18px;
        }

        .brand {
            font-size: 21px;
            font-weight: bold;
            margin-bottom: 38px;
        }

        .nav-item {
            display: block;
            padding: 12px 20px;
            margin-bottom: 6px;
            border-radius: 10px;
            color: #dbe4f0;
            text-decoration: none;
            font-size: 15px;
        }

        .nav-item:hover,
        .nav-item.active {
            background: #344967;
            color: white;
        }

        main {
            flex: 1;
            min-width: 0;
            padding: 32px;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 28px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        .muted {
            color: #718096;
            font-size: 14px;
        }

        .button {
            border: 0;
            border-radius: 8px;
            padding: 12px 16px;
            background: #3659d9;
            color: white;
            font-weight: bold;
            cursor: pointer;
        }

        .button:hover { background: #2848bd; }

        .button:disabled {
            opacity: .6;
            cursor: wait;
        }

        .cards {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 18px;
            margin-bottom: 26px;
        }

        .card, .panel {
            background: white;
            border: 1px solid #e6eaf0;
            border-radius: 12px;
            padding: 22px;
        }

        .card-value {
            display: block;
            margin-top: 14px;
            font-size: 30px;
            font-weight: bold;
        }

        .panel h2 {
            font-size: 18px;
            margin: 0 0 20px;
        }

        .table-wrap { overflow-x: auto; }

        table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
        }

        th, td {
            padding: 15px 12px;
            border-bottom: 1px solid #edf0f5;
            font-size: 14px;
        }

        th {
            color: #718096;
            font-weight: 600;
            background: #fafbfe;
        }

        .badge {
            display: inline-block;
            padding: 6px 9px;
            border-radius: 20px;
            background: #dcfce7;
            color: #166534;
            font-size: 12px;
        }

        .badge.low {
            background: #fef3c7;
            color: #92400e;
        }

        .message {
            margin: 16px 0 0;
            color: #718096;
            font-size: 14px;
        }

        @media (max-width: 800px) {
            .sidebar {
                width: 170px;
                padding: 20px 10px;
            }

            main { padding: 20px 14px; }
            .cards { grid-template-columns: 1fr; }
        }

        @media (max-width: 520px) {
            .layout { display: block; }
            .sidebar { width: 100%; padding: 16px; }
            .brand { margin-bottom: 15px; }

            .nav-item {
                display: inline-block;
                padding: 10px;
                margin-right: 3px;
            }
        }
    </style>
</head>

<body>
<div class="layout">
    <aside class="sidebar">
        <div class="brand">MALLOW</div>
        <a class="nav-item" href="/dashboard">Dashboard</a>
        <a class="nav-item active" href="/inventory">Inventory</a>
        <a class="nav-item" href="/orders">Orders</a>
        <a class="nav-item" href="/customers">Customers</a>
    </aside>

    <main>
        <header class="header">
            <div>
                <h1>Product Inventory</h1>
                <div class="muted">
                    Monitor product prices, tax rates, and available stock.
                </div>
            </div>

            <button
                class="button"
                id="refresh-button"
                type="button"
                onclick="refreshDashboard()"
            >
                Refresh Inventory
            </button>
        </header>

        <section class="cards">
            <div class="card">
                <div class="muted">Total Products</div>
                <span class="card-value" id="total-products">—</span>
            </div>

            <div class="card">
                <div class="muted">Low Stock Products (below 10)</div>
                <span class="card-value" id="low-stock-count">—</span>
            </div>
        </section>

        <section class="panel">
            <h2>Product Inventory</h2>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Unit Price</th>
                            <th>Tax %</th>
                            <th>Stock</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody id="product-table-body">
                        <tr>
                            <td colspan="6" class="muted">Loading products...</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <p id="inventory-message" class="message" role="status"></p>
        </section>
        
        <section class="panel">
            <h2>Low Stock Products</h2>
            <p class="muted">Products with fewer than 10 units available.</p>

            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Available Stock</th>
                        </tr>
                    </thead>
                    <tbody id="low-stock-table-body">
                        <tr>
                            <td colspan="3" class="muted">Loading low-stock products...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

    </main>
</div>

<script>
    async function loadProducts() {
        const tableBody = document.getElementById('product-table-body');
        const message = document.getElementById('inventory-message');

        try {
            const response = await fetch('/api/products', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error('Unable to load products.');
            }

            const result = await response.json();
            const products = result.products || [];

            document.getElementById('total-products').textContent =
                products.length;

            tableBody.replaceChildren();

            if (products.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 6;
                cell.textContent = 'No products found.';
                row.appendChild(cell);
                tableBody.appendChild(row);
                return products;
            }

            products.forEach(product => {
                const row = document.createElement('tr');

                [
                    product.name,
                    product.code,
                    '₹' + Number(product.price).toFixed(2),
                    Number(product.tax_percentage).toFixed(2) + '%',
                    product.stock_on_hand
                ].forEach(value => {
                    const cell = document.createElement('td');
                    cell.textContent = value;
                    row.appendChild(cell);
                });

                const statusCell = document.createElement('td');
                const badge = document.createElement('span');
                const isLowStock = Number(product.stock_on_hand) < 10;

                badge.className = isLowStock ? 'badge low' : 'badge';
                badge.textContent = isLowStock ? 'Low Stock' : 'In Stock';

                statusCell.appendChild(badge);
                row.appendChild(statusCell);
                tableBody.appendChild(row);
            });

            message.textContent = `${products.length} product(s) loaded.`;
            return products;
        } catch (error) {
            tableBody.replaceChildren();

            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 6;
            cell.textContent = 'Unable to load products. Please try again.';
            row.appendChild(cell);
            tableBody.appendChild(row);

            message.textContent = error.message;
            throw error;
        }
    }

   
    async function loadLowStockCount() {
        const countElement = document.getElementById('low-stock-count');

        try {
            const response = await fetch('/api/products', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error('Unable to load products.');
            }

            const result = await response.json();
            const products = result.products || [];

            const count = products.filter(product =>
                Number(product.stock_on_hand) < 10
            ).length;

            countElement.textContent = count;
        } catch (error) {
            countElement.textContent = '—';
            console.error(error);
        }
    }


   
    async function refreshDashboard() {
        const button = document.getElementById('refresh-button');
        button.disabled = true;
        button.textContent = 'Refreshing...';

        try {
            await Promise.all([
                loadProducts(),
                loadLowStockCount(),
                loadLowStockProducts()
            ]);
        } catch (error) {
            console.error(error);
        } finally {
            button.disabled = false;
            button.textContent = 'Refresh Inventory';
        }
    }


    
    async function loadLowStockProducts() {
        const tableBody = document.getElementById('low-stock-table-body');

        try {
            const response = await fetch('/api/products', {
                headers: { 'Accept': 'application/json' }
            });

            if (!response.ok) {
                throw new Error('Unable to load low-stock products.');
            }

            const result = await response.json();
            const products = result.products || [];

            const lowStockProducts = products.filter(
                product => Number(product.stock_on_hand) < 10
            );

            tableBody.replaceChildren();

            if (lowStockProducts.length === 0) {
                const row = document.createElement('tr');
                const cell = document.createElement('td');
                cell.colSpan = 3;
                cell.textContent = 'No low-stock products found.';
                row.appendChild(cell);
                tableBody.appendChild(row);
                return;
            }

            lowStockProducts.forEach(product => {
                const row = document.createElement('tr');

                [product.name, product.code, product.stock_on_hand]
                    .forEach(value => {
                        const cell = document.createElement('td');
                        cell.textContent = value;
                        row.appendChild(cell);
                    });

                tableBody.appendChild(row);
            });
        } catch (error) {
            tableBody.replaceChildren();

            const row = document.createElement('tr');
            const cell = document.createElement('td');
            cell.colSpan = 3;
            cell.textContent = 'Unable to load low-stock products.';
            row.appendChild(cell);
            tableBody.appendChild(row);

            console.error(error);
        }
    }

    refreshDashboard();
</script>
</body>
</html>
