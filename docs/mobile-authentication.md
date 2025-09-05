# Autenticación para clientes móviles

Los clientes móviles deben autenticarse para interactuar con los recursos de `orders`.

1. **Obtener token de sucursal**
   - Cada sucursal posee un `access_key` que funciona como token.
   - Este valor se entrega de forma segura al cliente móvil.
2. **Enviar token en cada petición**
   - Incluir el encabezado HTTP:
     ```
     Authorization: Bearer <access_key>
     ```
   - Alternativamente, los meseros que usan la interfaz web mantienen la sesión PHP con `$_SESSION['branch_id']`.
3. **Validación en el servidor**
   - El `OrderController` verifica primero `$_SESSION['branch_id']`.
   - Si no existe, comprueba el token contra `branches.access_key`.
   - Una combinación válida permite continuar la solicitud; de lo contrario se responde `401 No autorizado`.

Este flujo asegura que solo clientes autorizados puedan consultar o modificar pedidos.
