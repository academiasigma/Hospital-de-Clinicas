import os

# Extensiones de código que querés incluir
EXTENSIONS = {'.php', '.js', '.html', '.css', '.sql'}

# Carpetas que querés ignorar para no ensuciar el archivo final
IGNORE_DIRS = {'.git', 'vendor', 'node_modules', '__pycache__', '.idea', '.vscode'}

def generate_tree_and_bundle(root_dir, output_file):
    with open(output_file, 'w', encoding='utf-8') as outfile:
        outfile.write("# Árbol de Directorios del Proyecto\n\n```text\n")
        
        # 1. Generar la estructura de carpetas y archivos
        for dirpath, dirnames, filenames in os.walk(root_dir):
            # Filtrar directorios ignorados
            dirnames[:] = [d for d in dirnames if d not in IGNORE_DIRS]
            
            rel_path = os.path.relpath(dirpath, root_dir)
            if rel_path == '.':
                level = 0
                outfile.write(f"{os.path.basename(os.path.abspath(root_dir))}/\n")
            else:
                level = rel_path.count(os.sep) + 1
                indent = '│   ' * (level - 1) + '├── '
                outfile.write(f"{indent}{os.path.basename(dirpath)}/\n")
            
            sub_indent = '│   ' * level + '├── '
            for f in sorted(filenames):
                _, ext = os.path.splitext(f)
                if ext.lower() in EXTENSIONS:
                    outfile.write(f"{sub_indent}{f}\n")
        
        outfile.write("```\n\n---\n\n# Código Fuente de los Archivos\n\n")

        # 2. Agregar el contenido de cada archivo con su respectiva ruta
        for dirpath, dirnames, filenames in os.walk(root_dir):
            dirnames[:] = [d for d in dirnames if d not in IGNORE_DIRS]
            for f in sorted(filenames):
                _, ext = os.path.splitext(f)
                if ext.lower() in EXTENSIONS:
                    file_path = os.path.join(dirpath, f)
                    rel_path = os.path.relpath(file_path, root_dir)
                    
                    outfile.write(f"## Ruta: `{rel_path}`\n\n")
                    
                    # Detectar el lenguaje para el formato Markdown
                    lang = ext[1:]
                    if lang == 'js': lang = 'javascript'
                    elif lang == 'sql': lang = 'sql'
                    elif lang == 'php': lang = 'php'
                    else: lang = ''
                    
                    outfile.write(f"```{lang}\n")
                    try:
                        with open(file_path, 'r', encoding='utf-8') as infile:
                            outfile.write(infile.read())
                    except Exception as e:
                        outfile.write(f"// Error al leer el archivo: {e}\n")
                    outfile.write("\n```\n\n---\n\n")
    
    print(f"¡Éxito! Archivo unificado generado en: {output_file}")

if __name__ == "__main__":
    # Toma la carpeta actual como la raíz del proyecto
    project_root = "." 
    output_filename = "proyecto_unificado.md"
    generate_tree_and_bundle(project_root, output_filename)