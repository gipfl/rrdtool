```sh
rrdtool create simple-temperature.rrd \
 DS:temperature:GAUGE:8640:U:U \
 RRA:AVERAGE:0.5:1:2880 \
 RRA:AVERAGE:0.5:5:2880 \
 RRA:AVERAGE:0.5:30:4320 \
 RRA:AVERAGE:0.5:360:5840
```
