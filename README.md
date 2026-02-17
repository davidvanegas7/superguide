# 📚 SuperGuide

**SuperGuide** es una plataforma educativa de programación construida con Laravel. Permite crear y publicar guías de aprendizaje estructuradas en distintos lenguajes de programación, donde el contenido de cada lección se escribe en archivos **Markdown** y se renderiza con formato enriquecido.

🌐 **Demo en vivo:** [https://superguide.davidvanegasdev.com](https://superguide.davidvanegasdev.com)

---

## ✨ Características

- **Contenido en Markdown** — Las lecciones se escriben en archivos `.md` o directamente en el editor del panel de administración. Soporta GitHub Flavored Markdown: tablas, código con resaltado, listas de tareas y más.
- **Multi-lenguaje** — Organiza el contenido por lenguaje de programación (PHP, JavaScript, Python, TypeScript, SQL, etc.) con colores e íconos personalizados.
- **Cursos y lecciones** — Estructura jerárquica: Lenguaje → Curso → Lecciones, con niveles (principiante, intermedio, avanzado).
- **Seguimiento de progreso** — Los usuarios pueden marcar lecciones como completadas. El progreso se guarda por sesión sin necesidad de registro.
- **Búsqueda** — Buscador de lecciones por título, extracto y contenido.
- **Panel de administración** — CRUD completo para lenguajes, cursos y lecciones. Carga de archivos `.md` o editor de texto enriquecido.
- **Etiquetas** — Sistema de tags para categorizar lecciones.
- **Diseño limpio** — Interfaz construida con Tailwind CSS v4 y el plugin de tipografía para una lectura cómoda del contenido.

---

## 🛠️ Stack tecnológico

| Tecnología | Uso |
|---|---|
| **Laravel 12** | Framework backend |
| **MySQL** | Base de datos |
| **Tailwind CSS v4** | Estilos |
| **league/commonmark** | Parser de Markdown (GFM) |
| **Vite** | Bundler de assets |
| **Apache + Let's Encrypt** | Servidor web + SSL |

---

## 🚀 Instalación local

```bash
# 1. Clonar el repositorio
git clone git@github.com:davidvanegas7/superguide.git
cd superguide

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias Node
npm install

# 4. Configurar el entorno
cp .env.example .env
php artisan key:generate

# 5. Configurar la base de datos en .env
# DB_CONNECTION=mysql
# DB_HOST=127.0.0.1
# DB_PORT=3306
# DB_DATABASE=superguide
# DB_USERNAME=tu_usuario
# DB_PASSWORD=tu_password

# 6. Crear la base de datos y correr migraciones
php artisan migrate

# 7. Cargar datos de ejemplo
php artisan db:seed

# 8. Compilar assets
npm run build

# 9. Iniciar el servidor
php artisan serve
```

---

## 📁 Estructura del contenido

Las lecciones pueden tener su contenido en dos formas:

1. **Archivo `.md`** — Guarda el archivo en `content/lessons/` y referencia la ruta en el campo `md_file_path` de la lección.
2. **Editor en admin** — Escribe el contenido directamente en el campo `content_md` desde el panel de administración en `/admin`.

---

## 🗄️ Estructura de la base de datos

```
languages
  └── courses
        └── lessons ←── lesson_tag ──→ tags
              └── progress
```

---

## 📄 Licencia

MIT
