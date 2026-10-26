const health = document.querySelector('#health');
const form = document.querySelector('#message-form');
const formStatus = document.querySelector('#form-status');
const messages = document.querySelector('#messages');

async function loadHealth() {
  try {
    const response = await fetch('/api/health');
    const data = await response.json();
    health.textContent = response.ok ? 'API и база данных доступны' : 'Сервис временно недоступен';
    health.dataset.state = response.ok ? 'ok' : 'error';
  } catch {
    health.textContent = 'Не удалось подключиться к API';
    health.dataset.state = 'error';
  }
}

async function loadMessages() {
  try {
    const response = await fetch('/api/messages');
    if (!response.ok) throw new Error('Request failed');
    const items = await response.json();
    messages.replaceChildren();
    if (!items.length) {
      const li = document.createElement('li');
      li.textContent = 'Пока сообщений нет.';
      messages.append(li);
      return;
    }
    for (const item of items) {
      const li = document.createElement('li');
      const title = document.createElement('strong');
      title.textContent = item.name;
      const body = document.createElement('p');
      body.textContent = item.message;
      const date = document.createElement('small');
      date.textContent = new Date(item.created_at).toLocaleString();
      li.append(title, body, date);
      messages.append(li);
    }
  } catch {
    messages.textContent = 'Не удалось загрузить сообщения.';
  }
}

form.addEventListener('submit', async (event) => {
  event.preventDefault();
  formStatus.textContent = 'Отправляем…';
  const payload = {
    name: form.elements.name.value.trim(),
    message: form.elements.message.value.trim(),
  };
  try {
    const response = await fetch('/api/messages', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(payload),
    });
    const data = await response.json();
    if (!response.ok) throw new Error(data.error || 'Ошибка отправки');
    form.reset();
    formStatus.textContent = 'Сообщение сохранено.';
    await loadMessages();
  } catch (error) {
    formStatus.textContent = error.message || 'Ошибка отправки.';
  }
});

loadHealth();
loadMessages();
