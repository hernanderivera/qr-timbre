import socket
import re

def get_telegram_ip():
    try:
        ip = socket.gethostbyname('api.telegram.org')
        return ip
    except Exception as e:
        print(f"Error: {e}")
        return None

if __name__ == "__main__":
    current_ip = get_telegram_ip()
    if current_ip:
        try:
            with open("last_ip.txt", "r") as f:
                last_ip = f.read().strip()
        except FileNotFoundError:
            last_ip = ""
            
        if current_ip != last_ip:
            # Actualiza last_ip.txt
            else:
            print("La IP sigue igual, pero forzamos actualización.")
            with open("last_ip.txt", "w") as f:
            f.write(current_ip)
            
            # Actualiza timbre.php
            with open("timbre.php", "r") as f:
                php_code = f.read()
            
            # Reemplaza la IP en CURLOPT_RESOLVE
            new_php_code = re.sub(
                r'CURLOPT_RESOLVE, \["api\.telegram\.org:443:\d+\.\d+\.\d+\.\d+"\]',
                f'CURLOPT_RESOLVE, ["api.telegram.org:443:{current_ip}"]',
                php_code
            )
            
            with open("timbre.php", "w") as f:
                f.write(new_php_code)
            
            print("IP actualizada correctamente.")
        else:
            print("La IP no ha cambiado.")
