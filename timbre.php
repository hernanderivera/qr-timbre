<?php
// =============================================
// CONFIGURACIÓN PRINCIPAL
// =============================================
$token = '7586164341:AAHUG9JEw7pZWfPyKvah726OMZrtkw-Gn_M';      // Placeholder para el token
$chat_id = '-4644780147';       // Placeholder para el chat ID
$mensaje_default = "🔔 ¡Alguien está en la puerta!";
$audio_dir = 'audios/';

// Crear directorio de audios si no existe
if (!file_exists($audio_dir)) {
    mkdir($audio_dir, 0755, true);
}

// =============================================
// MANEJO DE GRABACIONES DE AUDIO (POST)
// =============================================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['audio'])) {
    header('Content-Type: application/json');
    
    try {
        // 1. Validar archivo de audio
        if ($_FILES['audio']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception('Error en la subida del audio: ' . $_FILES['audio']['error']);
        }

        // 2. Configurar la IP fija de Telegram
        $telegram_ip = '149.154.167.220'; // IP principal de Telegram API
        $telegram_url = "https://$telegram_ip/bot{$token}/sendVoice";
        
        // 3. Preparar los datos para cURL
        $postData = [
            'chat_id' => $chat_id,
            'caption' => 'Mensaje de voz del timbre'
        ];
        
        // 4. Configurar cURL con resolución manual
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $telegram_url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => array_merge(
                $postData,
                ['voice' => new CURLFile($_FILES['audio']['tmp_name'], 'audio/ogg', 'mensaje.ogg')]
            ),
            CURLOPT_HTTPHEADER => [
                "Host: api.telegram.org",
                "Content-Type: multipart/form-data"
            ],
            CURLOPT_RESOLVE => ["api.telegram.org:443:149.154.167.220"],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => 0,
        ]);

        // 5. Ejecutar y verificar
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Error en conexión: ' . curl_error($ch));
        }
        
        if ($http_code != 200) {
            $error_info = json_decode($response, true);
            throw new Exception(
                'Error de Telegram (' . $http_code . '): ' . 
                ($error_info['description'] ?? 'Respuesta inesperada')
            );
        }

        echo json_encode(['success' => true]);
        
    } catch (Exception $e) {
        error_log('Error al enviar audio: ' . $e->getMessage());
        echo json_encode([
            'error' => $e->getMessage(),
            'details' => 'Intente nuevamente o contacte al soporte técnico'
        ]);
    } finally {
        if (isset($ch)) curl_close($ch);
        if (isset($_FILES['audio']['tmp_name'])) {
            @unlink($_FILES['audio']['tmp_name']);
        }
    }
    exit;
}

// =============================================
// ENVÍO DE NOTIFICACIÓN PRINCIPAL (GET)
// =============================================
$contador = file_exists('contador.txt') ? (int)file_get_contents('contador.txt') : 0;
file_put_contents('contador.txt', $contador + 1);

$mensajes = [
    "🔔 ¡Hay alguien en la puerta!",
    "🚪 ¡Llegó una visita!",
    "👋 ¡Por favor ábreme!"
];
$mensaje = $mensajes[array_rand($mensajes)];

$ch = curl_init();
curl_setopt_array($ch, [
    CURLOPT_URL => "https://api.telegram.org/bot{$token}/sendMessage",
    CURLOPT_RESOLVE => ["api.telegram.org:443:149.154.167.220"],// El script reemplazará 0.0.0.0
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => http_build_query([
        'chat_id' => $chat_id,
        'text' => $mensaje,
        'parse_mode' => 'HTML'
    ]),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 5
]);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Timbre QR</title>
    <style>
        :root {
            --hego-blue: #0066cc;
            --hego-green: #4CAF50;
            --hego-red: #F44336;
            --hego-gray: #607D8B;
        }
        body {
            font-family: 'Arial', sans-serif;
            background: #f5f7fa;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            text-align: center;
        }
        .card {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            max-width: 400px;
            width: 90%;
        }
        .icono {
            font-size: 60px;
            margin-bottom: 20px;
            color: <?= ($http_code == 200) ? 'var(--hego-green)' : 'var(--hego-red)' ?>;
        }
        h1 {
            color: var(--hego-blue);
            margin-bottom: 10px;
            font-size: 24px;
        }
        .btn {
            background: var(--hego-blue);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 5px;
            cursor: pointer;
            margin: 10px;
            font-size: 16px;
            transition: background 0.3s;
        }
        .btn:hover {
            background: #0052a3;
        }
        .btn-audio {
            background: #9C27B0;
        }
        .btn-audio:hover {
            background: #7B1FA2;
        }
        .btn-audio.grabando {
            background: var(--hego-red);
            animation: pulse 1.5s infinite;
        }
        #audioStatus {
            margin-top: 20px;
            display: none;
        }
        #tiempo {
            font-weight: bold;
            display: inline-block;
            margin: 10px 0;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="icono"><?= ($http_code == 200) ? '✓' : '✗' ?></div>
        <h1><?= ($http_code == 200) ? '¡Notificación enviada!' : 'Error al enviar' ?></h1>
        <p><?= ($http_code == 200) 
            ? 'La notificación ha sido enviada correctamente.' 
            : 'No se pudo enviar la notificación. Intenta nuevamente.' ?>
        </p>
        
        <div class="contador">
            Este timbre ha sido usado <?= $contador ?> veces
        </div>
        
        <button class="btn" onclick="window.location.href='timbre.php'">
            Volver a tocar el timbre
        </button>
        
        <button id="btnGrabar" class="btn btn-audio">
            🎤 Dejar mensaje de voz
        </button>
        
        <div id="audioStatus">
            <p>Grabando... <span id="tiempo">0:00</span></p>
            <button id="btnEnviar" class="btn" disabled>
                Enviar audio
            </button>
        </div>
    </div>

    <script>
        // =============================================
        // SISTEMA DE GRABACIÓN DE AUDIO
        // =============================================
        let mediaRecorder;
        let audioChunks = [];
        let startTime;
        let timerInterval;

        // Función del temporizador (definida primero)
        function updateTimer() {
            const seconds = Math.floor((Date.now() - startTime) / 1000);
            document.getElementById('tiempo').textContent = 
                `${Math.floor(seconds / 60)}:${String(seconds % 60).padStart(2, '0')}`;
        }

        document.getElementById('btnGrabar').addEventListener('click', async () => {
            const btn = document.getElementById('btnGrabar');
            
            if (!mediaRecorder) {
                try {
                    // 1. Obtener acceso al micrófono
                    const stream = await navigator.mediaDevices.getUserMedia({ 
                        audio: {
                            echoCancellation: true,
                            noiseSuppression: true,
                            sampleRate: 16000
                        }
                    });

                    // 2. Detectar formato compatible
                    const mimeTypes = [
                        'audio/webm;codecs=opus',
                        'audio/ogg;codecs=opus',
                        'audio/mp4',
                        'audio/mpeg'
                    ];
                    
                    const supportedMimeType = mimeTypes.find(mimeType => 
                        MediaRecorder.isTypeSupported(mimeType)
                    ) || 'audio/webm'; // Fallback seguro

                    // 3. Configurar grabador
                    mediaRecorder = new MediaRecorder(stream, {
                        audioBitsPerSecond: 128000,
                        mimeType: supportedMimeType
                    });
                    
                    // 4. Configurar eventos
                    mediaRecorder.ondataavailable = e => {
                        if (e.data.size > 0) audioChunks.push(e.data);
                    };
                    
                    mediaRecorder.onstop = () => {
                        clearInterval(timerInterval);
                        stream.getTracks().forEach(track => track.stop());
                    };
                    
                    // 5. Actualizar UI
                    btn.textContent = '⏹ Detener grabación';
                    btn.classList.add('grabando');
                    document.getElementById('audioStatus').style.display = 'block';
                    document.getElementById('btnEnviar').disabled = true;
                    
                    // 6. Iniciar temporizador
                    startTime = Date.now();
                    timerInterval = setInterval(updateTimer, 1000);
                    updateTimer();
                    
                    // 7. Comenzar grabación
                    audioChunks = [];
                    mediaRecorder.start();

                } catch (error) {
                    alert(`Error al acceder al micrófono: ${error.message}`);
                    console.error('Error de grabación:', error);
                }
            } else {
                // Detener grabación
                mediaRecorder.stop();
                btn.textContent = '🎤 Grabar mensaje';
                btn.classList.remove('grabando');
                document.getElementById('btnEnviar').disabled = false;
            }
        });

        document.getElementById('btnEnviar').addEventListener('click', async () => {
            const btnEnviar = document.getElementById('btnEnviar');
            btnEnviar.disabled = true;
            btnEnviar.textContent = 'Enviando...';
            
            try {
                // 1. Determinar extensión del archivo
                const format = mediaRecorder.mimeType.includes('webm') ? 'webm' :
                              mediaRecorder.mimeType.includes('ogg') ? 'ogg' :
                              mediaRecorder.mimeType.includes('mp4') ? 'mp4' : 'mp3';

                // 2. Crear blob de audio
                const audioBlob = new Blob(audioChunks, { type: mediaRecorder.mimeType });

                // 3. Validar tamaño (máximo 10MB)
                if (audioBlob.size > 10 * 1024 * 1024) {
                    throw new Error('El audio es demasiado grande (máximo 10MB)');
                }

                // 4. Enviar al servidor
                const formData = new FormData();
                formData.append('audio', audioBlob, `mensaje.${format}`);

                const response = await fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                });
                
                const result = await response.json();
                
                if (result.error) {
                    throw new Error(result.error);
                }
                
                if (!result.success) {
                    throw new Error('Error al procesar el audio');
                }

                // 5. Notificar éxito
                alert('✅ Mensaje de voz enviado correctamente');
                document.getElementById('audioStatus').style.display = 'none';

            } catch (error) {
                alert(`❌ Error: ${error.message}`);
                console.error('Error al enviar audio:', error);
            } finally {
                btnEnviar.textContent = 'Enviar audio';
                btnEnviar.disabled = false;
            }
        });
    </script>
</body>
</html>
