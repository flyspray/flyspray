<?php
$translation = array(
'installFSdevelop'   => 'Parece que estás instalando una versión de desarrollo de Flyspray.',
'needcomposer'       => 'Es necesario instalar algunas librerías usando Composer.',
'installcomposer'    => 'Ejecutar Composer',
'performupgrade'     => 'Perform Upgrade',
'precautions'        => 'Advertencias',
'precautionbackup'   => 'Antes de actualizar se recomienta realizar una copia de seguridad de ficheros y base de datos de Flyspray.',
'preconditionchecks' => 'Precondition checks',
'upgrade'            => 'Actualizar',
'upgradepossible'    => 'Parece que hay una actualización disponible',
'versioncompare'     => 'Your current version is <strong>%s</strong> and the version we can upgrade to is <strong>%s</strong>.',
'writeaccessconf'    => 'Para actualizar Flyspray el fichero <pre>flyspray.conf.php</pre> debe tener permisos de escritura',
'adminemail'         => 'Email del administrador',
'adminusername'      => 'Usuario administrador',
'adminpassword'      => 'Contraseña del administrador',
'slogan'             => 'The bug Killer!',
'progress'           => 'Progress',
'documents'          => 'Docs',
'preinstallcheck'    => 'Pre-instalacion',
'databasesetup'      => 'Base de Datos',
'administration'     => 'Aplicación',
'installflyspray'    => 'Instalación Flyspray',
'libcheck'           => 'PHP y módulos requeridos',
'libchecktext'       => 'Para poder completar correctamente la instalación de Flyspray, debes tener instalado una versión de PHP y <strong>al menos una</strong> base de datos compatible.',
'recsettings'        => 'Configuración recomendada',
'recsettingstext1'   => 'A continuación se indica la configuración de PHP recomendada para asegurar la compatibilidad de Flyspray.',
'recsettingstext2'   => 'Nota: No obstante, es posible que Flyspray funcione con una configuración diferente a la recomendada.',
'dirandfileperms'    => 'Permisos en carpetas y ficheros',
'dirandfilepermstext'=> 'Para poder utilizar Flyspray se necesita tener acceso de escritura en determinados ficheros y carpetas.
                         Si se muestra el mensaje "Sin permiso de escritura" será necesario cambiar los permisos de forma que el servidor de Flyspray pueda escribir en ellos',
'proceedtodbsetup'   => 'Ir a Configuración de Base de Datos',
'proceedtodbsetuptext'=>'Cuando la configuración sea correcta, continua al siguiente paso para configurar la base de datos.',
'library'            => 'Módulo',
'status'             => 'Estado',
'database'           => 'Base de datos',
'recommended'        => 'Recomendada',
'actual'             => 'Actual',
'yes'                => 'Sí',
'no'                 => 'No',
'explainwhatandwhyheader' => 'Formatting of task descriptions and comments has changed',
'explainwhatandwhycontent' => 'Previously those installations of Flyspray that didn\'t use dokuwiki formatting engine stored data as plain text. '
    . 'We now use HTML as the default and can try to add paragraph and line break tags to already existing data entries, so your data retains it\'s '
    . 'structure. But if your existing data already contains manually added HTML tags something probably goes wrong and you have some corrupted '
    . 'entries in your database that must be manually fixed. If unsure, answer "No", unless you can examine the situation before proceeding. '
    . 'If you are fluent in programming with PHP, see also at the end of setup/upgrade.php, look at what it does and possibly modify according to '
    . 'your needs. ',
'databaseconfiguration'=>'Configuración de la Base de Datos para ',
'proceedtoadmin'=>'Ir a la Configuración del Administrador',
'databasehostname'=>'Servidor ',
'databasehostnamehint'=>'Introduce el <strong>host del servidor </strong> de base de datos donde se instalará la BDD de Flyspray. Normalmente se usa "localhost" o una dirección IP.',
'databasetype'=>'Tipo ',
'databasetypehint'=>'Selecciona el <strong>tipo de base de datos</strong>. Si están disponible las opciones MySQL y MySQLi, seleccione esta última. Idem si su base de datos es MariaDB en lugar de MySQL',
'databasename'=>'Esquema (nombre)',
'databasenamehint'=>'Introduce el nombre de la base de datos (esquema). Si no existe Flyspray intentará crearlo por ti. Nota: Usa nombres simples sin espacios, ej. "flyspray".',
'databaseusername'=>'Usuario',
'databaseusernamehint'=>'Introduce <strong>el usuario y contraseña de la base de datos</strong>.
El configurador de Flyspray requiere un usuario que tenga permisos para crear el esquema de base de datos.
Si no estás seguro, por favor, consulta con tu administrador o proveedor de hosting.
Nota: Los servidores Xampp o Wampp, por defecto, se instalan con un usuario "root" sin password (vacío)',
'databasepassword'=>'Contraseña',
'databasepasswordhint'=>'',
'tableprefix'=>'Prefijo de las tablas',
'tableprefixhint'=>'[Opcional] puedes indicar un prefijo para evitar la colisión con tablas existentes. Se recomienda "flyspray_" o "fs_"',
'next'=>'Next',
'showpassword'            => 'Mostrar Password',
'lgpllicense'             => 'Licencia LGPL',
'installationguide'       => 'Guía de instalación',
'developermanual'         => "Manual del desarrollador",
'supported'               => 'Soportado',
'inphp'                   => 'en PHP',
'available'               => 'Sí',
'missing'                 => '--',
'writeable'               => 'Escribible',
'unwriteable'             => 'Sin permiso de escritura',
'on'                      => 'ON',
'off'                     => 'OFF',
'x'                       => 'X',
'true'                    => 'Verdadero',
'false'                   => 'Falso',
'directive'               => 'Directiva',
'enable'                  => 'Activo',
'disable'                 => 'Desactivado',
'administrationsetup'     => 'Configuración de la aplicación',
'setupapplicationvalue'   => '',
'adminsetuptip1'          => 'El esquema de base de datos de Flyspray ha sido creado. Por favor, sigue las instrucciones para completar la configuración de la aplicación.',
'adminsetuptip2'          => '1) Introduce  los valores de <strong>Usuario, correo electrónico y contraseña</strong> del usuario administrador de Flyspray. Puedes cambiar estos valores desde en la sección de administración de Flyspray.',
'adminsetuptip3'          => '2) Selecciona el formado de documentación. Importante: este valor es configurado durante la instalación y no puede ser cambiado posteriormente. Selecciona Text/Dokuwiki si no estás seguro de cual elegir.',
'syntaxtext'              => 'Sintaxis<br>Selecciona Text/dokuwiki si no estás seguro de cual elegir. Nota: El cambio de dokuwiki a Html es sencillo. Sin embargo el cambio de Html a dokuwiki u otro formato (como markdown) conlleva pérdidas de información, tanto contenido como formato.',
'scheduletitle'           => 'Recuerda añadir una entrada en el crontab que llame al script "scheduler.php" de forma periódica. El planificador puede ser activado/desactivado desde la sección de administración de Flyspray.',
'enablescheduling'        => 'Activar planificador de tareas',
'proceedtofinalsetup'     => 'Finalizar instalación',
'proceedtofinalsetuptext' => 'Continua para completar la instalación de Flyspray.',
'installstatus'           => 'Estado de la instalación',
'congratulations'         => 'Enhorabuena! Flyspray está instalado y listo para usar.',
'removesetupdirectory'    => 'Por favor, borra el directorio setup antes de seguir.',
'viewsite'                => 'Finalizar y acceder a Flyspray',
'proceedtoindex'          => 'Ir a la página principal de Flyspray',
);
?>
