<?php
// Загрузка и сохранение данных
function loadData($type) {
    $filename = "data/{$type}.json";
    return file_exists($filename) ? json_decode(file_get_contents($filename), true) : ['last_id' => 0, 'items' => []];
}

function saveData($type, $data) {
    file_put_contents("data/{$type}.json", json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// Обработка действий
$action = $_POST['action'] ?? '';
$current_menu = $_GET['menu'] ?? 'deals';
$selected_item = $_GET['item'] ?? null;

// Загрузка данных
$deals_data = loadData('deals');
$contacts_data = loadData('contacts');

// Обработка CRUD операций
if ($action === 'save') {
    $type = $_POST['type'];
    $id = $_POST['id'] ?? null;
    
    if ($type === 'deal') {
        $deal = [
            'id' => $id ?: ++$deals_data['last_id'],
            'name' => $_POST['name'],
            'amount' => (int)$_POST['amount'],
            'contacts' => array_map('intval', $_POST['contacts'] ?? [])
        ];
        $deals_data['items'][$deal['id']] = $deal;
        if (!$id) $deals_data['last_id'] = $deal['id'];
        saveData('deals', $deals_data);
    } elseif ($type === 'contact') {
        $contact = [
            'id' => $id ?: ++$contacts_data['last_id'],
            'first_name' => $_POST['first_name'],
            'last_name' => $_POST['last_name'],
            'deals' => array_map('intval', $_POST['deals'] ?? [])
        ];
        $contacts_data['items'][$contact['id']] = $contact;
        if (!$id) $contacts_data['last_id'] = $contact['id'];
        saveData('contacts', $contacts_data);
    }
    header("Location: ?menu=$current_menu" . ($selected_item ? "&item=$selected_item" : ""));
    exit;
}

if ($action === 'delete') {
    $type = $_POST['type'];
    $id = (int)$_POST['id'];
    if ($type === 'deal' && isset($deals_data['items'][$id])) {
        unset($deals_data['items'][$id]);
        saveData('deals', $deals_data);
    } elseif ($type === 'contact' && isset($contacts_data['items'][$id])) {
        unset($contacts_data['items'][$id]);
        saveData('contacts', $contacts_data);
    }
    header("Location: ?menu=$current_menu");
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>CRM Система</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; vertical-align: top; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .active { background-color: #fffacd; font-weight: bold; }
        a { text-decoration: none; color: #0066cc; }
        a:hover { text-decoration: underline; }
        .relation-table { width: 100%; border-collapse: collapse; margin: 5px 0; }
        .relation-table td { border: 1px solid #ccc; padding: 4px; }
        .menu-cell { width: 20%; background-color: #f8f8f8; }
        .list-cell { width: 25%; }
        .content-cell { width: 55%; }
        .content-row { display: table-row; }
        .content-row > div { display: table-cell; padding: 5px; border-bottom: 1px solid #eee; }
        .content-row > div:first-child { font-weight: bold; width: 30%; background-color: #f9f9f9; }
        button { margin: 5px; padding: 6px 12px; background: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button.delete { background: #f44336; }
        .modal { position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); display: none; }
        .modal-content { background-color: white; margin: 10% auto; padding: 20px; border-radius: 5px; width: 50%; max-width: 500px; position: relative; }
        .close { position: absolute; right: 15px; top: 10px; font-size: 24px; font-weight: bold; cursor: pointer; }
        label { display: block; margin: 10px 0; }
        input[type="text"], input[type="number"], select { width: 100%; padding: 6px; margin: 4px 0; border: 1px solid #ddd; border-radius: 4px; }
        select[multiple] { height: 100px; }
    </style>
</head>
<body>
    <h1>CRM Система</h1>
    
    <table>
        <thead>
            <tr>
                <th class="menu-cell">Меню</th>
                <th class="list-cell">Список</th>
                <th class="content-cell">Содержимое</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <!-- Меню -->
                <td class="menu-cell">
                    <table>
                        <tr><td class="<?= $current_menu === 'deals' ? 'active' : '' ?>">
                            <a href="?menu=deals">Сделки</a>
                        </td></tr>
                        <tr><td class="<?= $current_menu === 'contacts' ? 'active' : '' ?>">
                            <a href="?menu=contacts">Контакты</a>
                        </td></tr>
                        <tr><td><button onclick="showForm()">+ Добавить</button></td></tr>
                    </table>
                </td>
                
                <!-- Список -->
                <td class="list-cell">
                    <table>
                        <?php if ($current_menu === 'deals'): ?>
                            <?php foreach ($deals_data['items'] as $deal): ?>
                                <tr><td class="<?= $selected_item == $deal['id'] ? 'active' : '' ?>">
                                    <a href="?menu=deals&item=<?= $deal['id'] ?>"><?= htmlspecialchars($deal['name']) ?></a>
                                </td></tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <?php foreach ($contacts_data['items'] as $contact): ?>
                                <tr><td class="<?= $selected_item == $contact['id'] ? 'active' : '' ?>">
                                    <a href="?menu=contacts&item=<?= $contact['id'] ?>">
                                        <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                                    </a>
                                </td></tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </table>
                </td>
                
                <!-- Содержимое -->
                <td class="content-cell">
                    <?php if ($selected_item): ?>
                        <?php if ($current_menu === 'deals' && isset($deals_data['items'][$selected_item])): ?>
                            <?php $deal = $deals_data['items'][$selected_item]; ?>
                            <div class="content-row"><div>id сделки</div><div><?= $deal['id'] ?></div></div>
                            <div class="content-row"><div>Наименование</div><div><?= htmlspecialchars($deal['name']) ?></div></div>
                            <div class="content-row"><div>Сумма</div><div><?= number_format($deal['amount'], 0, '', ' ') ?> ₽</div></div>
                            <div class="content-row"><div>Контакты</div><div>
                                <table class="relation-table">
                                    <?php foreach ($deal['contacts'] as $contact_id): ?>
                                        <?php if (isset($contacts_data['items'][$contact_id])): ?>
                                            <?php $contact = $contacts_data['items'][$contact_id]; ?>
                                            <tr>
                                                <td>id контакта: <?= $contact_id ?></td>
                                                <td><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </table>
                            </div></div>
                            <button onclick="editItem('deal', <?= $deal['id'] ?>)">Редактировать</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="deal">
                                <input type="hidden" name="id" value="<?= $deal['id'] ?>">
                                <button type="submit" class="delete" onclick="return confirm('Удалить сделку?')">Удалить</button>
                            </form>
                            
                        <?php elseif ($current_menu === 'contacts' && isset($contacts_data['items'][$selected_item])): ?>
                            <?php $contact = $contacts_data['items'][$selected_item]; ?>
                            <div class="content-row"><div>id контакта</div><div><?= $contact['id'] ?></div></div>
                            <div class="content-row"><div>Имя</div><div><?= htmlspecialchars($contact['first_name']) ?></div></div>
                            <div class="content-row"><div>Фамилия</div><div><?= htmlspecialchars($contact['last_name']) ?></div></div>
                            <div class="content-row"><div>Сделки</div><div>
                                <table class="relation-table">
                                    <?php foreach ($contact['deals'] as $deal_id): ?>
                                        <?php if (isset($deals_data['items'][$deal_id])): ?>
                                            <?php $deal = $deals_data['items'][$deal_id]; ?>
                                            <tr>
                                                <td>id сделки: <?= $deal_id ?></td>
                                                <td><?= htmlspecialchars($deal['name']) ?></td>
                                            </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </table>
                            </div></div>
                            <button onclick="editItem('contact', <?= $contact['id'] ?>)">Редактировать</button>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="type" value="contact">
                                <input type="hidden" name="id" value="<?= $contact['id'] ?>">
                                <button type="submit" class="delete" onclick="return confirm('Удалить контакт?')">Удалить</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>Выберите элемент из списка</p>
                    <?php endif; ?>
                </td>
            </tr>
        </tbody>
    </table>

    <!-- Модальное окно -->
    <div id="modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideForm()">&times;</span>
            <h2 id="modal-title">Добавить</h2>
            <form id="item-form" method="POST">
                <input type="hidden" name="action" value="save">
                <input type="hidden" name="type" id="form-type">
                <input type="hidden" name="id" id="form-id">
                
                <div id="deal-fields">
                    <label>Наименование:* <input type="text" name="name" required></label>
                    <label>Сумма: <input type="number" name="amount" value="0"></label>
                    <label>Контакты:
                        <select name="contacts[]" multiple>
                            <?php foreach ($contacts_data['items'] as $contact): ?>
                                <option value="<?= $contact['id'] ?>">
                                    <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                
                <div id="contact-fields" style="display: none;">
                    <label>Имя:* <input type="text" name="first_name" required></label>
                    <label>Фамилия: <input type="text" name="last_name"></label>
                    <label>Сделки:
                        <select name="deals[]" multiple>
                            <?php foreach ($deals_data['items'] as $deal): ?>
                                <option value="<?= $deal['id'] ?>"><?= htmlspecialchars($deal['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </label>
                </div>
                
                <button type="submit">Сохранить</button>
            </form>
        </div>
    </div>

    <script>
        function showForm(type = null, id = null) {
            const modal = document.getElementById('modal');
            const formType = document.getElementById('form-type');
            const formId = document.getElementById('form-id');
            const modalTitle = document.getElementById('modal-title');
            const dealFields = document.getElementById('deal-fields');
            const contactFields = document.getElementById('contact-fields');
            
            if (type) {
                formType.value = type;
                formId.value = id || '';
                modalTitle.textContent = id ? 'Редактировать' : 'Добавить';
                
                if (type === 'deal') {
                    dealFields.style.display = 'block';
                    contactFields.style.display = 'none';
                } else {
                    dealFields.style.display = 'none';
                    contactFields.style.display = 'block';
                }
            }
            modal.style.display = 'block';
        }

        function hideForm() {
            document.getElementById('modal').style.display = 'none';
            document.getElementById('item-form').reset();
        }

        function editItem(type, id) {
            showForm(type, id);
        }

        window.onclick = function(event) {
            const modal = document.getElementById('modal');
            if (event.target === modal) hideForm();
        }

        document.querySelector('button').addEventListener('click', function() {
            const urlParams = new URLSearchParams(window.location.search);
            showForm(urlParams.get('menu') === 'deals' ? 'deal' : 'contact');
        });
    </script>
</body>
</html>