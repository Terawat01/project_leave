const WebSocket = require('ws');

const wss = new WebSocket.Server({ port: 8080 });

const clients = new Map();

wss.on('connection', function connection(ws, req) {
  const urlParams = new URLSearchParams(req.url.slice(1));
  const userId = urlParams.get('user_id');

  if (userId) {
    clients.set(userId, ws);
    console.log(`User ${userId} connected.`);
  }

  ws.on('message', function incoming(message) {
    const data = JSON.parse(message);
    console.log('received: %s', data.message);

    // ส่งข้อความไปหาผู้ใช้ที่เกี่ยวข้อง
    if (clients.has(data.emp_id)) {
      const targetWs = clients.get(data.emp_id);
      targetWs.send(JSON.stringify({
        title: data.title,
        message: data.message
      }));
    }
  });

  ws.on('close', function close() {
    for (let [key, value] of clients.entries()) {
      if (value === ws) {
        clients.delete(key);
        console.log(`User ${key} disconnected.`);
        break;
      }
    }
  });
});
console.log('WebSocket server is running on port 8080');