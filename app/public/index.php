<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/Database.php';

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');

$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);

if ($path === '/api/health') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        Database::connection()->query('SELECT 1');
        echo json_encode(['status' => 'ok', 'database' => 'connected']);
    } catch (Throwable $e) {
        http_response_code(503);
        echo json_encode(['status' => 'error', 'database' => 'unavailable']);
    }
    exit;
}

if ($path === '/api/messages' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json; charset=utf-8');

    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    if (stripos($contentType, 'application/json') === false) {
        http_response_code(415);
        echo json_encode(['error' => 'Content-Type must be application/json']);
        exit;
    }

    $body = json_decode(file_get_contents('php://input') ?: '', true);
    $name = trim((string)($body['name'] ?? ''));
    $message = trim((string)($body['message'] ?? ''));

    if ($name === '' || mb_strlen($name) > 100 || $message === '' || mb_strlen($message) > 2000) {
        http_response_code(422);
        echo json_encode(['error' => 'Name (max 100) and message (max 2000) are required']);
        exit;
    }

    try {
        $pdo = Database::connection();
        $stmt = $pdo->prepare('INSERT INTO messages (name, message) VALUES (:name, :message) RETURNING id, created_at');
        $stmt->execute(['name' => $name, 'message' => $message]);
        http_response_code(201);
        echo json_encode(['status' => 'saved', 'id' => $stmt->fetchColumn()]);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to save message']);
    }
    exit;
}

if ($path === '/api/messages' && $_SERVER['REQUEST_METHOD'] === 'GET') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        $stmt = Database::connection()->query(
            'SELECT id, name, message, created_at FROM messages ORDER BY id DESC LIMIT 50'
        );
        echo json_encode($stmt->fetchAll());
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Unable to load messages']);
    }
    exit;
}

if (str_starts_with($path, '/api/')) {
    http_response_code(404);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode(['error' => 'Not found']);
    exit;
}
?>
<!doctype html>
<html lang="ru">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Demo — PHP + PostgreSQL</title>
  <link rel="stylesheet" href="/assets/style.css">
  <script src="/assets/app.js" defer></script>
</head>
<body>
  <main class="container">
    <section class="hero">
      <p class="eyebrow">DEPLOYMENT DEMO</p>
      <h1>Сообщения работают.</h1>
      <p class="lead">Frontend на HTML/CSS/JavaScript, API на PHP, данные в PostgreSQL.</p>
      <div class="status" id="health" role="status">Проверяем API…</div>
    </section>
    <section class="panel">
      <h2>Оставить сообщение</h2>
      <form id="message-form">
        <label for="name">Имя</label>
        <input id="name" name="name" maxlength="100" required autocomplete="name">
        <label for="message">Сообщение</label>
        <textarea id="message" name="message" maxlength="2000" required rows="4"></textarea>
        <button type="submit">Отправить</button>
        <p id="form-status" role="status"></p>
      </form>
    </section>
    <section class="panel">
      <h2>Последние сообщения</h2>
      <ul id="messages" class="messages"><li>Загрузка…</li></ul>
    </section>
    <footer>Учебный проект · Docker Compose · Ansible · GitHub Actions</footer>
  </main>
</body>
</html>
