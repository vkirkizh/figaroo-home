import os
import socket
import time
import subprocess

home_script = '/var/www/home.php'

sock = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
sock.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)

while 1:
    sock.bind(('', 7777))
    sock.listen(1)

    try:
        while 1:
            conn, addr = sock.accept()
            conn.settimeout(3.0)

            try:
                data = b'';
                ts = time.time()
                flag = 0
                while not b'$' in data:
                    tmp = conn.recv(1024)
                    if tmp:
                        data += tmp
                    if time.time() - ts > 3:
                        flag = 1
                        break

                if flag:
                    continue

                data = data.decode('utf-8')
                data = data.replace(' ', '')
                data = data.replace('#', ' ')
                data = data.strip()
                os.system('php ' + home_script + ' ' + data)

            except Exception:

            finally:
                conn.close()

    except Exception:

    finally:
        sock.close()
