# Pruebas manuales de saneamiento en Orders.js

1. Simular respuesta del servidor con datos maliciosos desde la consola del navegador:
   ```javascript
   const orders = [{
     table_number: "<img src=x onerror=alert('xss')>",
     customer_name: "<script>alert('xss')</script>",
     customer_phone: "\" onclick=alert('xss') \"",
     created_at: "2024-01-01",
     status: "pendiente",
     id: 99
   }];
   window.fetch = () => Promise.resolve({ json: () => Promise.resolve(orders) });
   loadOrders();
   ```
2. Verificar en la interfaz que los valores aparecen como texto literal y que **no** se ejecuta ningún `alert`.
3. Repetir la prueba para cada campo y estado del pedido.
