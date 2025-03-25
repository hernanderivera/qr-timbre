# 🏠 Timbre QR - Sistema de Notificaciones Inteligente

![Demo del Timbre QR](https://hego.com.ar/assets/img/timbre-qr-demo.jpg)  
*Solución IoT para recibir notificaciones instantáneas cuando alguien toca tu timbre.*

---

## 🌟 Características Principales
- **Notificaciones en Telegram**: Alerta inmediata en tu móvil al escanear el QR.
- **Carcasa Impresa en 3D**: Diseño resistente a la intemperie (PLA/PETG).
- **Sin Costos Recurrentes**: Utiliza hosting gratuito y código abierto.
- **Configuración en 10 Minutos**: Fácil instalación sin conocimientos avanzados.

```mermaid
graph TD
    A[Usuario escanea QR] --> B{Servidor}
    B --> C[Envía solicitud a Telegram]
    C --> D((Notificación en móvil))
