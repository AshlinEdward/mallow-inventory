```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mallow Inventory | Orders</title>

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

        .page-header,
        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .page-header {
            margin-bottom: 25px;
        }

        .page-header h1 {
            margin: 0 0 8px;
        }

        .page-header p {
            margin: 0;
            color: #64748b;
        }

        button {
            border: 0;
            background: #2563eb;
            color: white;
            padding: 12px 18px;
            border-radius: 8px;
            cursor: pointer;
        }

        button:hover {
            opacity: 0.9;
        }

        button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .panel {
            background: white;
            padding: 22px;
            margin-top: 22px;
            border-radius: 12px;
            box-shadow: 0 3px 12px #0000000d;
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 700px;
        }

        th, td {
            padding: 13px;
            border-bottom: 1px solid #e5e7eb;
            text-align: left;
        }

        th {
            color: #64748b;
            font-size: 14px;
        }

        .secondary {
            background: #e8eef8;
            color: #243247;
        }

        .modal {
            display: none;
            position: fixed;
            inset: 0;
            background: #0008;
            padding: 20px;
            overflow-y: auto;
            z-index: 10;
        }

        .modal-content {
            max-width: 700px;
            margin: 30px auto;
            background: white;
            padding: 24px;
            border-radius: 12px;
        }

        .field {
            margin-bottom: 16px;
        }

        label {
            display: block;
            margin-bottom: 6px;
        }

        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
        }

        .message {
            margin-top: 12px;
            color: #64748b;
        }

        .actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }

        .actions select {
            flex: 2;
            min-width: 150px;
        }

        .actions input {
            flex: 1;
            min-width: 80px;
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
            <a href="/dashboard">Dashboard</a>
            <a href="/inventory">Inventory</a>
            <a href="/orders" class="active">Orders</a>
            <a href="/customers">Customers</a>
        </nav>
    </aside>

    <main class="main">
        <header class="page-header">
            <div>
                <h1>Orders</h1>
                <p>Manage customer orders and order details.</p>
            </div>

            <button id="open-create-order">+ Create Order</button>
        </header>

        <section class="panel">
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Customer</th>
                        <th>Email</th>
                        <th>Subtotal</th>
                        <th>Tax</th>
                        <th>Grand Total</th>
                        <th>Details</th>
                    </tr>
                </thead>

                <tbody id="orders-body">
                    <tr>
                        <td colspan="7">Loading orders...</td>
                    </tr>
                </tbody>
            </table>

            <p id="orders-message" class="message"></p>
        </section>
    </main>

    <div class="modal" id="order-modal">
        <div class="modal-content">
            <div class="topbar">
                <h2>Create Order</h2>
                <button type="button" class="secondary" id="close-create-order">
                    Close
                </button>
            </div>

            <form id="create-order-form">
                <div class="field">
                    <label for="customer-name">Customer name</label>
                    <input id="customer-name" required>
                </div>

                <div class="field">
                    <label for="customer-email">Customer email</label>
                    <input id="customer-email" type="email" required>
                </div>

                <div id="order-lines"></div>

                <div class="actions">
                    <button type="button" class="secondary" id="add-order-line">
                        + Add Product
                    </button>

                    <button type="submit">Submit Order</button>
                </div>

                <p id="create-order-message" class="message"></p>
            </form>
        </div>
    </div>

    <script>
        const ordersBody = document.getElementById('orders-body');
        const ordersMessage = document.getElementById('orders-message');
        const orderModal = document.getElementById('order-modal');
        const orderLines = document.getElementById('order-lines');

        async function loadOrders() {
            ordersBody.replaceChildren();
            ordersMessage.textContent = 'Loading orders...';

            try {
                const response = await fetch('/api/orders', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error('Unable to load orders.');
                }

                const result = await response.json();
                const orders = result.orders || [];

                ordersMessage.textContent = '';

                if (orders.length === 0) {
                    const row = document.createElement('tr');
                    const cell = document.createElement('td');
                    cell.colSpan = 7;
                    cell.textContent = 'No orders found yet.';
                    row.appendChild(cell);
                    ordersBody.appendChild(row);
                    return;
                }

                orders.forEach(order => {
                    const row = document.createElement('tr');

                    const values = [
                        '#' + order.id,
                        order.customer?.name || 'Unknown',
                        order.customer?.email || '—',
                        '₹' + Number(order.subtotal).toFixed(2),
                        '₹' + Number(order.tax_total).toFixed(2),
                        '₹' + Number(order.grand_total).toFixed(2)
                    ];

                    values.forEach(value => {
                        const cell = document.createElement('td');
                        cell.textContent = value;
                        row.appendChild(cell);
                    });

                    const detailsCell = document.createElement('td');
                    const detailsButton = document.createElement('button');
                    detailsButton.textContent = 'View Items';
                    detailsButton.className = 'secondary';

                    detailsButton.addEventListener('click', () => {
                        const existingRow = ordersBody.querySelector(
                            `[data-order-details="${order.id}"]`
                        );

                        if (existingRow) {
                            existingRow.remove();
                            detailsButton.textContent = 'View Items';
                            return;
                        }

                        const detailsRow = document.createElement('tr');
                        detailsRow.dataset.orderDetails = order.id;

                        const detailsContent = document.createElement('td');
                        detailsContent.colSpan = 7;

                        const list = document.createElement('ul');

                        (order.items || []).forEach(item => {
                            const listItem = document.createElement('li');
                            listItem.textContent =
                                `${item.product_name || 'Product'} — Quantity: ${item.quantity}`;
                            list.appendChild(listItem);
                        });

                        detailsContent.appendChild(list);
                        detailsRow.appendChild(detailsContent);
                        row.after(detailsRow);
                        detailsButton.textContent = 'Hide Items';
                    });

                    detailsCell.appendChild(detailsButton);
                    row.appendChild(detailsCell);
                    ordersBody.appendChild(row);
                });

                ordersMessage.textContent = `${orders.length} order(s) found.`;

            } catch (error) {
                ordersMessage.textContent = error.message;
                console.error(error);
            }
        }

        async function loadProductsIntoSelect(select) {
            const response = await fetch('/api/products');

            if (!response.ok) {
                throw new Error('Unable to load products.');
            }

            const data = await response.json();
            select.replaceChildren();

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = 'Select product';
            select.appendChild(placeholder);

            (data.products || []).forEach(product => {
                const option = document.createElement('option');
                option.value = product.id;
                option.textContent =
                    `${product.name} — Stock: ${product.stock_on_hand}`;
                option.disabled = Number(product.stock_on_hand) < 1;
                select.appendChild(option);
            });
        }

        async function addOrderLine() {
            const wrapper = document.createElement('div');
            wrapper.className = 'field';

            const label = document.createElement('label');
            label.textContent = 'Product and quantity';

            const row = document.createElement('div');
            row.className = 'actions';

            const select = document.createElement('select');
            select.required = true;

            const quantity = document.createElement('input');
            quantity.type = 'number';
            quantity.min = '1';
            quantity.step = '1';
            quantity.value = '1';
            quantity.required = true;

            const remove = document.createElement('button');
            remove.type = 'button';
            remove.className = 'secondary';
            remove.textContent = 'Remove';
            remove.addEventListener('click', () => wrapper.remove());

            row.append(select, quantity, remove);
            wrapper.append(label, row);
            orderLines.appendChild(wrapper);

            try {
                await loadProductsIntoSelect(select);
            } catch (error) {
                wrapper.remove();
                throw error;
            }
        }

        document.getElementById('open-create-order').addEventListener('click', async () => {
            orderModal.style.display = 'block';
            orderLines.replaceChildren();
            document.getElementById('create-order-message').textContent = '';

            try {
                await addOrderLine();
            } catch (error) {
                document.getElementById('create-order-message').textContent =
                    'Unable to load products. Please try again.';
            }
        });

        document.getElementById('close-create-order').addEventListener('click', () => {
            orderModal.style.display = 'none';
        });

        orderModal.addEventListener('click', event => {
            if (event.target === orderModal) {
                orderModal.style.display = 'none';
            }
        });

        document.getElementById('add-order-line').addEventListener('click', async () => {
            try {
                await addOrderLine();
            } catch (error) {
                document.getElementById('create-order-message').textContent =
                    'Unable to load products.';
            }
        });

        document.getElementById('create-order-form').addEventListener('submit', async event => {
            event.preventDefault();

            const message = document.getElementById('create-order-message');
            const submitButton = event.target.querySelector('[type="submit"]');
            const lines = [...orderLines.children];

            const products = lines.map(line => ({
                product_id: Number(line.querySelector('select').value),
                quantity: Number(line.querySelector('input').value)
            }));

            if (!products.length || products.some(item =>
                !item.product_id || !Number.isInteger(item.quantity) || item.quantity < 1
            )) {
                message.textContent = 'Please select a product and enter a valid quantity.';
                return;
            }

            message.textContent = 'Submitting order...';
            submitButton.disabled = true;

            try {
                const response = await fetch('/api/orders', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        customer: {
                            name: document.getElementById('customer-name').value,
                            email: document.getElementById('customer-email').value
                        },
                        products
                    })
                });

                const data = await response.json();

                if (!response.ok) {
                    const errors = data.errors
                        ? Object.values(data.errors).flat().join(' ')
                        : (data.message || 'Order could not be created.');

                    message.textContent = errors;
                    return;
                }

                orderModal.style.display = 'none';
                event.target.reset();
                orderLines.replaceChildren();

                await loadOrders();

            } catch (error) {
                message.textContent = 'Could not connect to the server.';
            } finally {
                submitButton.disabled = false;
            }
        });

        loadOrders();
    </script>
</body>
</html>