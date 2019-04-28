# openwrt-wifi-honeypot
Router OpenWRT con un falso portal cautivo para capturar contaseñas, como ejercicio de concienciación para las [JASYP'19](https://interferencias.tech/jasyp/).

## Presentación
[slides.pdf](slides.pdf)


Realizado con la colaboración de:
-  [@chelunike](https://github.com/chelunike)
-  [@mati3](https://github.com/mati3)

## Requisitos previos

Para este proyecto vamos a usar:

- Router [Huawei hg556a](https://openwrt.org/toh/huawei/hg556a) con OpenWrt instalado. O cualquier otro tipo de hardware con OpenWrt, como puede ser una raspberry pi.

![Image Hawei HG556A ](https://proxy.duckduckgo.com/iu/?u=http%3A%2F%2Fimages5.naptol.com%2Fusr%2Flocal%2Fcsp%2FstaticContent%2Fproduct_images%2Fhorizontal%2F750x750%2FHuawei-HG556a-1.jpg&f=1)

- Interfaz de red [TP-Link TLWN722N](https://www.tp-link.com/us/home-networking/usb-adapter/tl-wn722n/)

- Los archivos de configuración se encuentran en el repositorio siguiendo la misma estructura de archivos que en el mismo OpenWrt.

- Y una conferencia de ciberseguridad llena de personas con ansia de wifi.

## Guia de Instalación

### 0. Intalar OpenWrt en el Router y los paquetes necestarios

De este paso no nos encargamos nosotros :)
Simplemente comentar que cada hardware es difenrente luego el proceso de instalación es diferente. Luego para la instalación hay que buscar si esta soportado y su correspondiente versión de instalación.

[Guia de Instalacion de OpenWrt](https://openwrt.org/docs/guide-user/installation/generic.flashing)

Tras tener nuestro OpenWrt instalado podemos empezar a instalar los paquetes necesarios. Para ello el router debe estar conectado a internet, ya sea por cable o por wifi.

Pasos para instalar los paquetes
1. Descargar la lista de repositorios, ya que esta solo se mantiene temporalmente para ahorrar memoria. Se puede realizar por su propia interfaz `System>> Sofware >> Boton de 'Update List'`.  O bien por consola por comando:
```bash
opkg update
```

2. Pasamos a instalar el paquete `nodogsplash` el cual va a ser quien genere el portal cautivo.
La instalación de paquetes también se puede realizar tanto por interfaz en `System >> Sofware`, donde aparece la lista de paquetes. O bien por consola, en ambos caso el nombre de los paquetes es el mismo.
```bash
opkg install nodogsplash
```

3. Instalamos `nginx` el servidor web que nos va a procesar las peticiones de login.
```bash
opkg install nginx
```

4. Instalamos `php7`, `php7-cgi` y `php7-mod-json` el interprete de php que nos procesara las petición la almacenará y luego nos lo devolvera.
```bash
opkg install php7 php7-cgi php7-mod-json
```

5. Ya que vamos a usar una interfaz de red externa, necesitamos los drivers especificos de esta en nuestro es una `Qualcomm Atheros Communications AR9271` y los paquetes necesarios:
```bash
opkg install usbutils # Para tener el siguiente comando
lsusb # Para ver nuestra interfaz .
opkg install kmod-ath9k-htc ath9k-htc-firmware
wifi config # Para detectar la interfaz de red
ip addr show # Y si vemos nuestra interfaz es buena señal
```

### 1. Configurar las redes

En el OpenWrt vamos a configurar dos interfaces, una interfaz lan para las posibles conexiones por cable que queramos hacer para conectar por ssh. Y una interfaz wlan para la red wifi. Para esta configuración se puede hacer desde la misma interfaz en `Network>>Interface>> Add new interface`. O bien a traves del archivo de configuración situado en `/etc/config/network` y el cual se encuentra en el repositorio.

Con lo cual tendriamos las siguientes redes:

- Interfaz: lan
	- IP: 192.168.1.222
	- Mascara de red: 255.255.255.0
	- Puerta de enlace: 192.168.1.1
	- Dns: 8.8.8.8
	- Y con la interfaz fisica hacia los propios puertos ethernet del router
	- Sin servidor dhcp

- Interfaz: wlan
	- IP: 172.16.0.1
	- Mascara de red: 255.240.0.0
	- Puerta de enlace: 192.168.1.222
	- Dns: 192.168.1.222
	- Y con la interfaz fisica hacia la interfaz wifi
	- Con su servidor dhcp ( sirviendo desde la .10 hasta .200, para tener muchos visitantes :)

Tras configurar las interfaces proseguimos configurando la red wifi.
- Wifi: 'JASYP_FR33_WIFI'
	- En la interfaz nuestra `wlan0`
	- Sin ningun tipo de autentificación
	- Sin ponerla en oculto, queremos que la gente se conecte

### 2. Configurar nodogsplash

Ahora pasamos a configurar el portal cautivo como tal. Para ello se puede copiar el archivo de configuración del repositorio. Y copiamos el splash.html y css que se van a servir en la ventana de login. Los cambios que hay en diferencia al original son:

- Eliminación de los comentarios sobrantes.
- Cambiada interfaz en la que escucha por nuestra interfaz. 
`option gatewayinterface 'wlan0'`
- Agreagar nuestro html y css al directorio configurado.
`option webroot '/etc/nodogsplash/htdocs'`
- Agregadas reglas para permitir el acceso al servidor nginx que sirve en el puerto 8080
```
list authenticated_users 'allow tcp port 8080'
list preauthenticated_users 'allow tcp port 8080'
list users_to_router 'allow tcp port 8080'
```

Por otro lado para que el nodogsplash funcione correctamente sin acceso a internet hay que agregar una linea de configuración al dnsmasq con la dirección del servidor. Situado en `/etc/dnsmasq.conf`.
```
address=/#/172.16.0.1
``` 

Finalmente iniciamos el servicio y lo configuramos para inicio automático.

```
/etc/init.d/nodosplash start  # Iniciamos servicio
/etc/init.d/nodosplash enable # Se inicia al arranque
```

### 3. Configura el servidor nginx

Para el servidor web la configuración, también esta disponible en el repositorio (`/etc/nginx/nginx.conf`), consiste simplemente en:

- Configurar el puerto 8080
- Sirver el directorio /www
- Y agregar el index.php al archivo que sirve por defecto.
- Y configurar el php desde el archivo por defecto es descomentar unas lineas.

Luego de configurarlo reiniciamos el servicio.
```
/etc/init.d/nginx restart
```
Y copiamos los archivos que sirven al directorio `/www`.

### 4. Configuración para guardar los datos de conexión

Los datos de login se guardan en `/logs`. Y dentro de este directorio se tiene que encontrar:
- logins.txt
- dhcp.leases

El archivo `logins.txt` donde se guardan las peticiones del formulario, el cual debe estar creado.

Y el archivo `dhcp.leases` donde se guardan los registros del servidor dhcp, el cual tambié se crea. Para ello accedemos a su archivo de configuración, también adjunto, `/etc/config/dhcp` y modificamos la siguiente linea:
```
option leasefile '/logs/dhcp.leases'
```

Acabamos reinicinando el servicio para aplicar cambios
```
/etc/init.d/odhcp restart
```

### 5. Aseguramos nuestro router.

Para dejar el router seguro, vamos a quitar la autentificación por contraseña en el ssh. Para ello metemos nuestras claves publicas.
```
vi /etc/dropbear/authorized_keys
```

Y Quitamos la entrada por contraseña:
```
uci set dropbear.@dropbear[0].PasswordAuth=off
uci set dropbear.@dropbear[0].RootPasswordAuth=off
uci commit dropbear
```

### 6. Nos sentamos y esperamos a que alguien entre.

Además contamos con un `data.php` el cual recibe un token, el cual se puede cambiar, que lee los archivos que guardan los datos y te los devuelve en json.

