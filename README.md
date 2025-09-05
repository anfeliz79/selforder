# SelfOrder

## Configuración

El proyecto utiliza variables de entorno para establecer la conexión a la base de datos. Debes definir las siguientes variables:

- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`

Puedes copiar el archivo `.env.example` y renombrarlo a `.env` para establecer estos valores:

```bash
cp .env.example .env
```

Completa cada variable con los datos correspondientes a tu entorno. Si alguna variable falta al intentar conectarse, la aplicación mostrará un mensaje de advertencia y no se iniciará la conexión.
