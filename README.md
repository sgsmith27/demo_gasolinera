# ⛽ Sistema de Gestión para Gasolinera

Sistema web integral para la gestión operativa, administrativa y financiera de una gasolinera, desarrollado con Laravel. Incluye control de ventas, inventario de combustible, turnos de despachadores, cuentas por cobrar/pagar, reportes y auditoría.

---

## 🚀 Características principales

### 🔹 Operación

* Registro de ventas de combustible
* Control de precios por tipo de combustible
* Gestión de bombas y mangueras
* Control de inventario por tanque
* Registro de abastecimientos
* Ajustes manuales de inventario
* Anulación de ventas y abastecimientos

### 🔹 Turnos

* Apertura y cierre de turnos
* Asignación de despachadores
* Control de efectivo inicial (fondo de cambio)
* Asignación de bomba fija o libre
* Reportes por turno

### 🔹 Administración

* Gestión de gastos
* Gestión de clientes
* Gestión de proveedores
* Cuentas por cobrar (ventas a crédito)
* Cuentas por pagar (deudas con proveedores)

### 🔹 Reportes

* Reporte de ventas
* Reporte de gastos
* Reporte de inventario
* Reporte de abastecimientos
* Bitácora de auditoría (acciones del sistema)
* Balance operativo (financiero)
* Exportación a PDF

### 🔹 Seguridad

* Sistema de roles (admin, supervisor, despachador)
* Auditoría completa de acciones
* Control de sesiones con expiración por inactividad
* Protección contra acciones no autorizadas

---

## 📊 Balance operativo

El sistema incluye un reporte financiero que calcula:

```
Balance = Ventas + Cobros CxC - Gastos - Pagos CxP
```

Permite analizar la rentabilidad del negocio por rango de fechas.

---

## 🧰 Tecnologías utilizadas

* PHP 8+
* Laravel 12
* PostgreSQL
* TailwindCSS
* JavaScript (Vanilla)
* DomPDF (para generación de reportes PDF)

---

## ⚙️ Instalación

### 1. Clonar repositorio

```bash
git clone https://github.com/tu-usuario/tu-repositorio.git
cd tu-repositorio
```

### 2. Instalar dependencias

```bash
composer install
npm install
npm run build
```

### 3. Configurar entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos

Editar `.env`:

```env
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=gasolinera_db
DB_USERNAME=postgres
DB_PASSWORD=tu_password
```

### 5. Migrar base de datos

```bash
php artisan migrate
```

### 6. Ejecutar servidor

```bash
php artisan serve
```

---

## 🔐 Configuración de sesión (seguridad)

El sistema incluye cierre automático por inactividad.

En `.env`:

```env
SESSION_LIFETIME=30
SESSION_EXPIRE_ON_CLOSE=false
```

---

## 👥 Roles del sistema

| Rol         | Acceso               |
| ----------- | -------------------- |
| Admin       | Acceso completo      |
| Supervisor  | Operación y reportes |
| Despachador | Registro de ventas   |

---

## 📂 Estructura de módulos

* **Ventas** → Registro y control de ventas
* **Inventario** → Tanques y combustible
* **Turnos** → Control de caja y despachadores
* **Gastos** → Egresos operativos
* **Cuentas por cobrar** → Clientes a crédito
* **Cuentas por pagar** → Proveedores
* **Reportes** → Información financiera y operativa
* **Auditoría** → Registro de acciones

---

## 📄 Exportación a PDF

El sistema permite exportar:

* Reportes de ventas
* Reportes de gastos
* Cuentas por cobrar
* Cuentas por pagar
* Bitácoras
* Balance operativo

---

## 🧪 Estado del proyecto

✅ Sistema funcional
✅ Listo para uso en gasolineras pequeñas/medianas
🔜 Integración de facturación electrónica (FEL)

---

## 💰 Modelo de implementación sugerido

* Instalación por cliente
* Hosting independiente por gasolinera
* Pago inicial + mensualidad

---

## 📌 Próximas mejoras

* Facturación electrónica FEL (Guatemala)
* Impresión de tickets térmicos
* Reporte de cierre diario de caja
* Exportación a Excel
* Multi-estación (multi-sucursal)

---

## 👨‍💻 Autor

**Sergio**
Ingeniero en Sistemas
Especialización en Inteligencia Artificial

---

## 📜 Licencia

Este proyecto puede ser utilizado con fines educativos o comerciales bajo acuerdo del autor.
