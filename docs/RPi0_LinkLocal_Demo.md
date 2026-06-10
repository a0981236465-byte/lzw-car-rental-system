# Raspberry Pi Zero 2W Link-Local Demo Notes

## Requirement

The demo should use only link-local networking, with WiFi off.

## Suggested Demo Steps

1. Turn off WiFi on the Raspberry Pi.
2. Connect the Raspberry Pi and the demo computer through the available direct network method.
3. Make sure the Raspberry Pi receives a link-local address in the `169.254.x.x` range.
4. Start Apache and MariaDB.
5. Open the web application from the demo computer.

Example:

```text
http://169.254.x.x/lzw-car-rental-system/public/
```

If hostname resolution works, this may also work:

```text
http://raspberrypi.local/lzw-car-rental-system/public/
```

## Useful Commands

Check IP address:

```bash
ip addr
```

Check WiFi status:

```bash
rfkill list
```

Block WiFi:

```bash
sudo rfkill block wifi
```

Unblock WiFi after demo:

```bash
sudo rfkill unblock wifi
```

Check Apache:

```bash
sudo systemctl status apache2
```

Check MariaDB:

```bash
sudo systemctl status mariadb
```
