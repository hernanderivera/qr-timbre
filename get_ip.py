import requests
import socket

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
        with open("last_ip.txt", "r") as f:
            last_ip = f.read().strip()
        
        if current_ip != last_ip:
            with open("last_ip.txt", "w") as f:
                f.write(current_ip)
            
            # Actualiza timbre.php
            with open("timbre.php", "r") as f:
                php_code = f.read()
            
            new_php_code = php_code.replace(last_ip, current_ip)
            
            with open("timbre.php", "w") as f:
                f.write(new_php_code)
            
            print("IP actualizada correctamente.")
        else:
            print("La IP no ha cambiado.")
