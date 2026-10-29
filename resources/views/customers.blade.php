```html
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mallow Inventory | Customers</title>

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

        .page-heading {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 22px;
        }

        h1 {
            margin: 0 0 8px;
            font-size: 28px;
        }

        h2 {
            margin-top: 0;
        }

        .muted {
            color: #788399;
            font-size: 14px;
        }

        .panel {
            background: white;
            border: 1px solid #e6eaf2;
            border-radius: 12px;
            padding: 22px;
            margin-bottom: 24px;
            box-shadow: 0 3px 12px #0000000d;
        }

        .search {
            width: 100%;
            max-width: 420px;
            padding: 12px 14px;
            border: 1px solid #d9dfeb;
            border-radius: 8px;
            font-size: 14px;
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }

        th, td {
            padding: 15px 12px;
            text-align: left;
            border-bottom: 1px solid #edf0f5;
            font-size: 14px;
        }

        th {
            color: #68748b;
            font-size: 12px;
            text-transform: uppercase;
            background: #fafbfe;
        }

        .button {
            border: 0;
            border-radius: 7px;
            padding: 10px 13px;
            background: #2563eb;
            color: white;
            cursor: pointer;
            font-weight: 600;
        }

        .button:hover {
            opacity: 0.9;
        }

        .message {
            color: #788399;
            padding: 18px 0;
            font-size: 14px;
        }

        .order-card {
            border: 1px solid #e6eaf2;
            border-radius: 9px;
            padding: 16px;
            margin-top: 14px;
        }

        .order-top {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px;
        }

        .order-items {
            color: #56627a;
            font-size: 14px;
        }

        .hidden {
            display: none;
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

            .page-heading {
                align-items: stretch;
            }

            .search {
                max-width: 100%;
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
            <a href="/orders">Orders</a>
            <a href="/customers" class="active">Customers</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-heading">
            <div>
                <h1>Customers</h1>
                <div class="muted">
                    Manage customers and view their order history.
                </div>
            </div>

            <input
                type="search"
                id="customer-search"
                class="search"
                placeholder="Search by customer name or email..."
            >
        </div>

        <section class="panel">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Customer</th>
                            <th>Email</th>
                            <th>Total Orders</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody id="customers-body"></tbody>
                </table>
            </div>

            <p id="customers-message" class="message">
                Loading customers...
            </p>
        </section>

        <section id="history-panel" class="panel hidden">
            <h2 id="history-title">Order History</h2>
            <div id="history-content"></div>
        </section>
    </main>

    <script>
        const customersBody = document.getElementById('customers-body');
        const customersMessage = document.getElementById('customers-message');
        const searchInput = document.getElementById('customer-search');
        const historyPanel = document.getElementById('history-panel');
        const historyTitle = document.getElementById('history-title');
        const historyContent = document.getElementById('history-content');

        let customers = [];

        async function loadCustomers() {
            customersMessage.textContent = 'Loading customers...';

            try {
                const response = await fetch('/api/customers', {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error('Unable to load customers.');
                }

                const result = await response.json();
                customers = result.customers || [];
                renderCustomers(customers);

            } catch (error) {
                customersBody.replaceChildren();
                customersMessage.textContent = error.message;
            }
        }

        function renderCustomers(list) {
            customersBody.replaceChildren();

            if (list.length === 0) {
                customersMessage.textContent = 'No customers found.';
                return;
            }

            customersMessage.textContent =
                `${list.length} customer(s) found.`;

            list.forEach(customer => {
                const row = document.createElement('tr');

                const nameCell = document.createElement('td');
                nameCell.textContent = customer.name;

                const emailCell = document.createElement('td');
                emailCell.textContent = customer.email;

                const countCell = document.createElement('td');
                countCell.textContent = customer.orders_count ?? 0;

                const actionCell = document.createElement('td');
                const button = document.createElement('button');

                button.className = 'button';
                button.textContent = 'View Orders';
                button.addEventListener('click', () => viewOrders(customer));

                actionCell.appendChild(button);
                row.append(nameCell, emailCell, countCell, actionCell);
                customersBody.appendChild(row);
            });
        }

        searchInput.addEventListener('input', () => {
            const term = searchInput.value.trim().toLowerCase();

            const filtered = customers.filter(customer =>
                customer.name.toLowerCase().includes(term) ||
                customer.email.toLowerCase().includes(term)
            );

            renderCustomers(filtered);
        });

        async function viewOrders(customer) {
            historyPanel.classList.remove('hidden');
            historyTitle.textContent = `Order History — ${customer.name}`;
            historyContent.textContent = 'Loading order history...';

            try {
                const response = await fetch(
                    `/api/customers/orders?email=${encodeURIComponent(customer.email)}`,
                    { headers: { 'Accept': 'application/json' } }
                );

                const result = await response.json();

                if (!response.ok) {
                    throw new Error(
                        result.message || 'Unable to load order history.'
                    );
                }

                historyContent.replaceChildren();

                if (!result.orders || result.orders.length === 0) {
                    historyContent.textContent =
                        'This customer has no orders yet.';
                    return;
                }

                result.orders.forEach(order => {
                    const card = document.createElement('div');
                    card.className = 'order-card';

                    const top = document.createElement('div');
                    top.className = 'order-top';

                    const orderNumber = document.createElement('strong');
                    orderNumber.textContent = `Order #${order.id}`;

                    const total = document.createElement('strong');
                    total.textContent =
                        `Total: ₹${Number(order.grand_total).toFixed(2)}`;

                    top.append(orderNumber, total);
                    card.appendChild(top);

                    const totals = document.createElement('p');
                    totals.className = 'muted';
                    totals.textContent =
                        `Subtotal: ₹${Number(order.subtotal).toFixed(2)} | ` +
                        `Tax: ₹${Number(order.tax_total).toFixed(2)}`;

                    card.appendChild(totals);

                    const itemsTitle = document.createElement('strong');
                    itemsTitle.textContent = 'Items';
                    card.appendChild(itemsTitle);

                    const itemsList = document.createElement('ul');
                    itemsList.className = 'order-items';

                    (order.items || []).forEach(item => {
                        const li = document.createElement('li');
                        li.textContent =
                            `${item.product_name || 'Product'} — Quantity: ${item.quantity}`;
                        itemsList.appendChild(li);
                    });

                    card.appendChild(itemsList);
                    historyContent.appendChild(card);
                });

            } catch (error) {
                historyContent.textContent = error.message;
            }

            historyPanel.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }

        loadCustomers();
    </script>
</body>
</html>