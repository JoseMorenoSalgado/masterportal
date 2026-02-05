local_masterportal v0.4.4 (Moodle 5.1+)

- Accesos rápidos salen de SECCIONES del "curso base" (por defecto ID=2).
- Puedes cambiar el ID del curso base desde el plugin (porque en otro Moodle puede cambiar).
- Cada tarjeta puede tener imagen subida desde el plugin.
- Se omite la sección general (sección 0) para accesos rápidos.

Config:
1) Administración del sitio -> Plugins -> Plugins locales -> Portal Máster
   - Curso base (ID)

2) Administración del sitio -> Plugins -> Plugins locales -> Gestionar accesos rápidos
   - Título + número de sección + imagen.

Update:
- Reemplaza /local/masterportal/
- Admin -> Notificaciones
- Purgar cachés


Fix v0.4.4:
- Evita conflicto con theme RemUI (parametro 'section' era un array). Se renombraron campos del formulario.

Fix v0.4.4:
- Formulario de accesos rápidos usa nombres de campos planos (evita problemas al guardar / arrays).
- Menú lateral en móvil ahora es tipo hamburguesa (off-canvas) manteniendo el fondo.

Fix v0.4.4:
- Menú hamburguesa ahora funciona sin AMD (usa js_init_code).
- manage_quick ya no usa admin_externalpage_setup para evitar choques con RemUI (HTTP 500).
- Ajustes extra para móvil en tarjeta de bienvenida.

Fix v0.4.4:
- manage_quick usa contexto de usuario (evita HTTP 500 en RemUI por admin_get_root/settingsnav).
- Menú móvil responsivo: items en columnas, texto a 2 líneas sin cortar.
- Hero (Bienvenido) en móvil: CTA en ancho completo y sin recortes.

Fix v0.4.4:
- Menú móvil: items en 1 columna, sin truncar textos (sin 'Dashb...'), nombre del sitio en 2 líneas.

Fix v0.4.4:
- Menú móvil: fuerza 1 columna incluso si el contenedor usa grid/columns.
- Menú: muestra enlace 'Administración' solo para administradores del sitio.

Fix v0.4.4:
- Menú móvil: override ultra-agresivo contra layouts en 2 columnas (grid/flex-wrap/columns) dentro del sidebar.

Fix v0.4.4:
- Menú: 'Administración del sitio' visible solo para admin en escritorio y móvil.

Fix v0.4.4:
- Admin: enlace 'Administración del sitio' debajo de Comunidad (solo admins).
- Menú de usuario flotante propio (avatar o iniciales) con Perfil/Calificaciones/Calendario/Cerrar sesión.

Fix v0.4.6:
- Accesos rápidos: cada tarjeta puede dirigir a una categoría (categoryid) en vez de un curso.

Fix v0.5.1:
- Calendario: se usa el mini calendario nativo de Moodle (sin iframe) adaptado al tamaño del dashboard.

Fix v0.5.1:
- Calendario: compatibilidad Moodle/RemUI - se eliminan llamadas a funciones no disponibles (calendar_get_default_groups/users) con fallback seguro.

Fix v0.5.1:
- Calendario temporal propio (auto cambia cada mes) para evitar incompatibilidades del mini-calendario nativo.

Fix v0.5.1:
- Calendario: siempre visible + marca días con eventos del calendario Moodle (puntos).
- Recursos: nueva página /local/masterportal/resources.php con tabs y grid tipo catálogo (imagen desde intro si existe, si no icono).
