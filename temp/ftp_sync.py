import os
import sys
import ftplib
import datetime

# Función sencilla para cargar variables de un archivo .env
def load_env(env_path='.env'):
    if os.path.exists(env_path):
        with open(env_path, 'r', encoding='utf-8') as f:
            for line in f:
                line = line.strip()
                if line and not line.startswith('#'):
                    if '=' in line:
                        key, value = line.split('=', 1)
                        # Limpiar comillas si las tiene
                        value = value.strip('\'"')
                        os.environ[key.strip()] = value.strip()

# Cargar las variables desde .env en el directorio actual
load_env()

FTP_HOST = os.environ.get("FTP_HOST")
FTP_USER = os.environ.get("FTP_USER")
FTP_PASS = os.environ.get("FTP_PASS")
REMOTE_DIR = os.environ.get("FTP_REMOTE_DIR")
LOCAL_DIR = os.environ.get("FTP_LOCAL_DIR", os.getcwd()) # Por defecto usa el directorio actual si no se especifica

def should_upload(ftp, local_file_path, filename):
    """
    Compara el tamaño y la fecha de modificación (si está disponible)
    para decidir si es necesario subir el archivo.
    """
    try:
        remote_size = ftp.size(filename)
        local_size = os.path.getsize(local_file_path)
        
        if remote_size != local_size:
            return True
            
        try:
            remote_time_str = ftp.sendcmd('MDTM ' + filename)[4:].strip()
            
            # Obtener fecha de modificación local en UTC utilizando timezone support proper timezone
            local_time = datetime.datetime.fromtimestamp(os.path.getmtime(local_file_path), datetime.UTC)
            local_time_str = local_time.strftime("%Y%m%d%H%M%S")
            
            if local_time_str > remote_time_str:
                return True
                
            return False
            
        except Exception:
            return False
            
    except Exception:
        return True

def upload_single_file(ftp, local_file_path, relative_path):
    remote_file_dir = os.path.dirname(relative_path)
    
    if remote_file_dir:
        remote_file_dir = remote_file_dir.replace('\\', '/')
        dirs = remote_file_dir.split('/')
        
        for d in dirs:
            if not d: continue
            try:
                ftp.cwd(d)
            except ftplib.error_perm:
                ftp.mkd(d)
                ftp.cwd(d)
                
    filename = os.path.basename(relative_path)
    
    if should_upload(ftp, local_file_path, filename):
        print(f"Subiendo: {relative_path}...")
        try:
            with open(local_file_path, "rb") as f:
                ftp.storbinary(f"STOR {filename}", f)
        except Exception as e:
            print(f"Error subiendo {filename}: {e}")
    else:
        print(f"Omitido (sin cambios): {relative_path}")
        
    ftp.cwd(REMOTE_DIR)


def upload_paths(paths_to_upload):
    if not FTP_HOST or not FTP_USER or not FTP_PASS or not REMOTE_DIR:
        print("Error: Faltan variables de entorno FTP (FTP_HOST, FTP_USER, FTP_PASS, FTP_REMOTE_DIR).")
        print("Asegúrate de tener un archivo .env configurado correctamente en la ruta atual.")
        return

    print(f"Conectando a {FTP_HOST}...")
    try:
        ftp = ftplib.FTP(FTP_HOST)
        ftp.login(FTP_USER, FTP_PASS)
        print("Conexión exitosa.")
        
        ftp.cwd(REMOTE_DIR)
        print(f"Cambiado al directorio remoto: {REMOTE_DIR}")
        
        for input_path in paths_to_upload:
            local_path = os.path.normpath(os.path.join(LOCAL_DIR, input_path))
            
            if os.path.isfile(local_path):
                upload_single_file(ftp, local_path, input_path)
                
            elif os.path.isdir(local_path):
                print(f"Procesando carpeta: {input_path}")
                for root, dirs, files in os.walk(local_path):
                    for file in files:
                        if file == '.DS_Store':
                            continue
                            
                        full_local_path = os.path.join(root, file)
                        rel_path = os.path.relpath(full_local_path, LOCAL_DIR)
                        upload_single_file(ftp, full_local_path, rel_path)
            else:
                print(f"La ruta local no existe: {local_path}")
                
        ftp.quit()
        print("Desconectado del FTP.")
        
    except Exception as e:
        print(f"Error durante la conexión o proceso subida: {e}")

if __name__ == "__main__":
    if len(sys.argv) > 1:
        paths = sys.argv[1:]
        upload_paths(paths)
    else:
        print("Por favor, proporciona al menos un archivo o carpeta para subir.")
        print("Uso: python3 ftp_sync.py ruta/al/archivo ruta/a/carpeta")
