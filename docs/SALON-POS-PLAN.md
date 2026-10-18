# Plan del POS para peluquerías

## Recomendación técnica

La peluquería se implementará como una nueva vertical dentro del mismo código, evitando mantener dos aplicaciones independientes y duplicar correcciones, seguridad y migraciones.

```text
pos          → Comercio
restaurant   → Restaurante
optic        → Óptica
salon        → Peluquería
```

Cada empresa con suscripción `salon` verá únicamente las funciones de peluquería. La vertical podrá desplegarse con dominio, nombre y diseño propio si se quiere comercializar como producto independiente.

## Objetivo

El POS para peluquerías permitirá:

- Administrar citas.
- Evitar cruces de horarios.
- Manejar estilistas y otros profesionales.
- Definir servicios, duración y precio.
- Vender servicios y productos en una misma operación.
- Controlar inventario de productos.
- Manejar caja.
- Permitir que clientes reserven citas públicamente.
- Permitir ventas públicas de productos.
- Consultar ventas, citas y rendimiento del negocio.

## Funcionalidades actuales que se conservan

### Base administrativa

- Empresas y sucursales.
- Usuarios, roles y permisos.
- Clientes.
- Configuración del negocio.
- Dominio público por empresa.
- Suscripciones.

### Operación

- POS.
- Apertura y cierre de caja.
- Métodos de pago.
- Ventas y comprobantes.
- Devoluciones de productos.
- Cartera opcional.
- Reportes.
- Gastos y contabilidad básica.

### Productos

- Categorías.
- Productos simples y variantes.
- Códigos de barras.
- Inventario por sucursal.
- Compras a proveedores.
- Stock mínimo.
- Movimientos de inventario.
- Ecommerce de productos.

El tipo de producto `service` existente se reutilizará para los servicios de peluquería. Los servicios no descontarán inventario directamente.

## Funciones que se ocultarán

Para empresas `salon` se ocultarán:

- Mesas, cocina y comandas de restaurante.
- Pedidos internos de restaurante.
- Pacientes de optometría.
- Fórmulas e historias clínicas.
- Órdenes médicas.
- Funciones específicas de kits gastronómicos.

Estas funciones no se eliminarán del proyecto; se bloquearán mediante contexto, permisos y navegación.

## Módulo de peluquería

### Profesionales

Cada profesional tendrá:

- Nombre y usuario asociado opcional.
- Sucursal.
- Fotografía.
- Teléfono y correo.
- Especialidades.
- Servicios habilitados.
- Estado activo o inactivo.
- Color para el calendario.
- Porcentaje de comisión opcional.
- Horario laboral.
- Descansos, días libres, vacaciones y ausencias.

Roles sugeridos:

- Administrador.
- Recepcionista.
- Profesional.
- Cajero.

Un profesional sin permisos administrativos solamente podrá consultar sus propias citas.

### Servicios

Cada servicio tendrá:

- Nombre y categoría.
- Descripción e imagen pública.
- Duración.
- Tiempo adicional de preparación o limpieza.
- Precio e impuesto.
- Profesionales habilitados.
- Sucursales donde se presta.
- Visibilidad pública.
- Estado.
- Instrucciones previas para el cliente.

Categorías iniciales:

- Corte.
- Barbería.
- Coloración.
- Peinados.
- Tratamientos capilares.
- Manicure y pedicure.
- Cejas y pestañas.
- Otros servicios.

### Horarios y disponibilidad

Se configurará:

- Horario de atención por sucursal.
- Horario individual por profesional.
- Duración de cada servicio.
- Tiempo de descanso entre citas.
- Anticipación mínima para reservar.
- Máximo de días futuros disponibles.
- Días festivos.
- Bloqueos manuales.
- Vacaciones y ausencias.
- Política de cancelación.

La disponibilidad se calculará utilizando:

```text
Sucursal abierta
+ profesional disponible
+ profesional habilitado para el servicio
+ duración completa disponible
+ tiempo de preparación
- citas existentes
- bloqueos y descansos
```

El servidor validará nuevamente el horario al guardar para impedir reservas simultáneas sobre el mismo espacio.

### Citas

Una cita tendrá:

- Empresa y sucursal.
- Cliente.
- Profesional.
- Uno o varios servicios.
- Fecha y hora de inicio.
- Hora estimada de finalización.
- Duración total.
- Precio estimado.
- Observaciones del cliente.
- Notas internas.
- Origen público, recepción o interno.
- Estado.
- Venta asociada opcional.

Estados:

```text
pending       → Pendiente de confirmación
confirmed     → Confirmada
checked_in    → Cliente presente
in_progress   → En servicio
completed     → Finalizada
cancelled     → Cancelada
no_show       → Cliente no asistió
```

Las citas canceladas o marcadas como no asistidas permanecerán en el historial.

### Agenda administrativa

La agenda tendrá:

- Calendario diario y semanal.
- Vista por profesional.
- Filtros por sucursal y estado.
- Colores por profesional.
- Creación manual de citas.
- Reprogramación y cancelación.
- Confirmación.
- Registro de llegada.
- Inicio y finalización del servicio.
- Bloqueo de horarios.
- Acceso rápido al cliente.

En teléfonos se utilizará una agenda diaria optimizada para pantallas pequeñas.

### Atención y cobro

```text
Cita confirmada
      ↓
Cliente llega
      ↓
Iniciar servicio
      ↓
Agregar servicios adicionales o productos
      ↓
Finalizar atención
      ↓
Enviar al POS
      ↓
Registrar pago
      ↓
Cerrar cita y generar venta
```

La venta podrá incluir:

- Servicios realizados.
- Productos vendidos.
- Descuentos.
- Propina opcional.
- Varios métodos de pago.
- Profesional responsable.
- Comisión calculada.

Los productos físicos descontarán inventario; los servicios no manejarán existencias. Cada venta originada desde una cita guardará `appointment_id`.

## Parte pública

### Página principal

Mostrará:

- Nombre y logotipo.
- Sucursales, direcciones y horarios.
- Teléfono y redes sociales.
- Galería.
- Servicios y profesionales destacados.
- Productos disponibles.
- Botón principal `Reservar cita`.

### Reserva pública

```text
Seleccionar sucursal
        ↓
Seleccionar uno o varios servicios
        ↓
Seleccionar profesional o “Cualquier profesional”
        ↓
Seleccionar fecha
        ↓
Seleccionar hora disponible
        ↓
Ingresar datos del cliente
        ↓
Aceptar política de cancelación
        ↓
Confirmar cita
```

Datos mínimos:

- Nombre.
- Teléfono.
- Correo electrónico.
- Observaciones opcionales.

La reserva no exigirá contraseña. Si el correo o teléfono coincide con un cliente existente, se asociará sin duplicarlo.

Después de reservar se mostrará:

- Número de cita.
- Fecha y hora.
- Sucursal y profesional.
- Servicios.
- Duración.
- Precio estimado.
- Política de cancelación.

### Confirmaciones

La primera versión incluirá:

- Confirmación en pantalla.
- Correo al cliente.
- Correo a la peluquería.
- Enlace seguro para consultar o cancelar.
- Aviso de nueva cita en el panel.

Para una etapa posterior:

- WhatsApp y SMS.
- Recordatorios automáticos.
- Confirmación mediante enlace.
- Lista de espera.

### Catálogo público de productos

El ecommerce actual se conservará para productos de la peluquería. La interfaz separará claramente:

```text
Reservar servicios
Comprar productos
```

Los servicios se reservarán en la agenda y no se añadirán al carrito tradicional. Los productos mantendrán catálogo, carrito, checkout, transferencia, QR, contraentrega o recogida en sucursal y seguimiento de pedidos.

No se ofrecerá pago automático con tarjeta hasta implementar una pasarela.

### Área del cliente

El cliente podrá consultar:

- Próximas citas y citas anteriores.
- Estado y detalle de la cita.
- Profesional y servicios.
- Reprogramación según política.
- Cancelación.
- Pedidos y compras de productos.

La cuenta será opcional para reservar, pero necesaria para consultar el historial completo.

## Modelo de datos

### `salon_professionals`

- Empresa.
- Usuario opcional.
- Sucursal principal.
- Identidad e información de contacto.
- Fotografía y color de agenda.
- Comisión y estado.

### `salon_services`

- Producto de tipo servicio.
- Duración y tiempo de preparación.
- Visibilidad pública.
- Instrucciones y estado.

### `salon_professional_services`

Relacionará profesionales con servicios y podrá guardar precio o duración especial.

### `salon_schedules`

Horarios recurrentes por profesional y día de la semana.

### `salon_schedule_blocks`

Descansos, vacaciones, permisos y bloqueos manuales.

### `salon_appointments`

Datos generales de la cita, profesional, cliente, horario, estado, origen y venta asociada.

### `salon_appointment_items`

Servicios reservados, duración y precio congelados al reservar.

### Ventas

Agregar:

- `salon_appointment_id`.
- Profesional responsable.
- Propina, si se incluye en la primera versión.

## Reportes

### Operativos

- Citas por día, profesional y servicio.
- Cancelaciones y clientes que no asistieron.
- Ocupación de agenda.
- Horas disponibles frente a ocupadas.

### Comerciales

- Ventas de servicios y productos.
- Ticket promedio.
- Servicios y productos más vendidos.
- Clientes frecuentes.
- Ingresos por profesional.
- Comisiones y propinas.

### Inventario

- Existencias.
- Stock mínimo.
- Compras.
- Movimientos.
- Productos más vendidos.

## Menú de peluquería

```text
Dashboard
Agenda
Citas
Profesionales
Servicios
Clientes
POS
Productos
Inventario
Compras
Caja
Ventas
Pedidos web
Reportes
Configuración
```

Contabilidad y cartera podrán activarse según el plan contratado.

## Dashboard

Mostrará:

- Citas de hoy y próxima cita.
- Citas pendientes de confirmar.
- Profesionales trabajando.
- Ocupación del día.
- Ventas y servicios realizados.
- Productos con stock bajo.
- Cancelaciones y no asistencias.
- Accesos rápidos para nueva cita y nueva venta.

## Permisos

```text
manage_salon_professionals
manage_salon_services
view_salon_calendar
manage_salon_appointments
manage_own_appointments
confirm_salon_appointments
cancel_salon_appointments
checkout_salon_appointments
manage_salon_schedules
view_salon_reports
```

También se creará middleware `salon.company` para impedir acceso desde otras verticales.

## Primera versión vendible

- Contexto de empresa `salon`.
- Profesionales.
- Servicios con duración.
- Horarios y bloqueos.
- Agenda diaria y semanal.
- Citas administrativas.
- Reserva pública.
- Confirmación por correo.
- Clientes.
- POS mixto de servicios y productos.
- Caja.
- Inventario y compras.
- Ecommerce de productos.
- Reportes básicos.
- Diseño móvil.

## Segunda etapa

- Lista de espera.
- Paquetes de sesiones.
- Membresías.
- Bonos y tarjetas regalo.
- Pagos anticipados y pasarela.
- Recordatorios por WhatsApp.
- Fidelización y puntos.
- Fotografías de antes y después.
- Fichas técnicas de coloración.
- Cabinas y equipos como recursos.
- Citas con varios profesionales.
- Liquidación avanzada de comisiones.
- Operación offline.

## Fases de implementación

### Fase 1: separación de la vertical

- Crear servicio `salon`.
- Configurar tipo de empresa, middleware y permisos.
- Adaptar navegación y dashboard.
- Ocultar restaurante y optometría.

### Fase 2: profesionales y servicios

- Crear modelos y tablas.
- CRUD de profesionales.
- Servicios y especialidades.
- Horarios, bloqueos y vacaciones.

### Fase 3: agenda interna

- Citas y validación de disponibilidad.
- Calendario diario y semanal.
- Estados, cancelación y reprogramación.
- Vista móvil.

### Fase 4: integración POS

- Convertir cita en venta.
- Mezclar servicios y productos.
- Relacionar inventario y profesional.
- Crear reportes iniciales.

### Fase 5: portal público

- Página especializada.
- Catálogo de servicios.
- Selección de profesional y disponibilidad.
- Reserva sin registro obligatorio.
- Consulta, cancelación y correos.

### Fase 6: productos públicos

- Adaptar ecommerce.
- Separar servicios y productos.
- Recogida en sucursal.
- Pedidos y seguimiento.

### Fase 7: calidad y salida

- Pruebas de conflictos simultáneos.
- Pruebas multiempresa y permisos.
- Pruebas móviles, de zona horaria y correo.
- Pruebas de venta desde cita.
- Piloto con una peluquería real.

## Criterios de aceptación

- Una peluquería solo ve sus módulos.
- Un cliente puede reservar desde el dominio público.
- No se permiten citas superpuestas para el mismo profesional.
- La disponibilidad respeta horarios, descansos y bloqueos.
- Una cita puede convertirse en venta.
- La venta puede mezclar servicios y productos.
- Los productos vendidos descuentan inventario.
- Los servicios no generan inventario.
- Se puede vender sin cita previa.
- Se puede cancelar o reprogramar una cita.
- Los datos están separados por empresa.
- La experiencia funciona desde teléfono.
- Los correos de confirmación se envían y registran.

La prioridad es construir primero **profesionales, servicios, agenda, reserva pública y conversión a venta**. El POS, inventario, compras, caja y ecommerce se reutilizarán del sistema actual.
