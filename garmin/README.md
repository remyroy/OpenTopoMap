# Building a custom Garmin map - a complete walkthrough

Based on the [HOWTO](HOWTO) this guide describes how to create a custom Garmin map.
using OpenTopoMap styles.

## Required tools & OpenTopoMap repository

```bash
git clone https://github.com/der-stefan/OpenTopoMap.git
cd OpenTopoMap/garmin
```

Download [mkgmap](http://www.mkgmap.org.uk/download/mkgmap.html),
[splitter](http://www.mkgmap.org.uk/download/splitter.html) & bounds
Download [phyghtmap](http://katze.tfiu.de/projects/phyghtmap/)

```bash
MKGMAP="mkgmap-r4919" # adjust to latest version (see www.mkgmap.org.uk)
SPLITTER="splitter-r653"

mkdir tools
pushd tools > /dev/null

if [ ! -d "${MKGMAP}" ]; then
    wget "http://www.mkgmap.org.uk/download/${MKGMAP}.zip"
    unzip "${MKGMAP}.zip"
fi
MKGMAPJAR="$(pwd)/${MKGMAP}/mkgmap.jar"

if [ ! -d "${SPLITTER}" ]; then
    wget "http://www.mkgmap.org.uk/download/${SPLITTER}.zip"
    unzip "${SPLITTER}.zip"
fi
SPLITTERJAR="$(pwd)/${SPLITTER}/splitter.jar"

popd > /dev/null

if stat --printf='' bounds/bounds_*.bnd 2> /dev/null; then
    echo "bounds already downloaded"
else
    echo "downloading bounds"
    rm -f bounds.zip  # just in case
    wget "http://osm.thkukuk.de/data/bounds-latest.zip"
    unzip "bounds-latest.zip" -d bounds
fi

BOUNDS="$(pwd)/bounds"

if stat --printf='' sea/sea/sea_*.pbf 2> /dev/null; then
    echo "sea already downloaded"
else
    echo "downloading sea"
    rm -f sea.zip  # just in case
    wget "http://osm.thkukuk.de/data/sea-latest.zip"
    unzip "sea-latest.zip" -d sea
fi

SEA="$(pwd)/sea/sea"
```

## Fetch map data, split & build garmin map

```bash
REMOTEPBF=quebec-latest.osm.pbf
REMOTEPOLY=quebec.poly
REMOTEROOT=http://download.geofabrik.de/north-america/canada/
REMOTEPBFURL=$REMOTEROOT$REMOTEPBF
REMOTEPOLYURL=$REMOTEROOT$REMOTEPOLY
MAPDESCRIPTION="Quebec"
UNIQUEID=0921
OUTFILENAME=osm-quebec.img
COUTFILENAME=osm-quebec-contour.img

mkdir data
pushd data > /dev/null

## Get OSM data

rm -f $REMOTEPBF
wget "$REMOTEPBFURL"

## Split map

rm -f 6324*.pbf
java -Xmx8g -jar $SPLITTERJAR \
    --precomp-sea=$SEA \
    "$(pwd)/${REMOTEPBF}"
DATA="$(pwd)/6324*.pbf"

## Get poly file

rm -f $REMOTEPOLY
wget "$REMOTEPOLYURL"

## Get contour/SRTM data

rm -f phyghtmap-out*.pbf
phyghtmap --polygon=$REMOTEPOLY -j 16 -s 10 -0 \
    --source=view3 \
    --max-nodes-per-tile=0 --max-nodes-per-way=0 --pbf -o phyghtmap-out
PHYGHTMAPPBF=`find . -type f -name "phyghtmap-out*" -print`

DEMPATH="$(pwd)/hgt/VIEW3"

## Split contour

rm -f 5324*.pbf
java -Xmx8g -jar $SPLITTERJAR \
    --precomp-sea=$SEA \
    --mapid=53240001 \
    --polygon-file=$REMOTEPOLY \
    $PHYGHTMAPPBF
CDATA="$(pwd)/5324*.pbf"

popd > /dev/null

OPTIONS="$(pwd)/opentopomap_options"
STYLEFILE="$(pwd)/style/opentopomap"
COPTIONS="$(pwd)/contours_options"
CSTYLEFILE="$(pwd)/style/contours"

pushd style/typ > /dev/null

## Create typ file

java -jar $MKGMAPJAR --family-id=35 opentopomap.txt
TYPFILE="$(pwd)/opentopomap.typ"

## Create contour typ

java -jar $MKGMAPJAR --family-id=36 contours.txt
CTYPFILE="$(pwd)/contours.typ"

popd > /dev/null

## Create Garmin map

java -Xmx8g -jar $MKGMAPJAR -c $OPTIONS --style-file=$STYLEFILE \
    --precomp-sea=$SEA \
    --description="$MAPDESCRIPTION - OSM $(date +'%d-%m-%Y')" \
    --series-name="$MAPDESCRIPTION - OSM $(date +'%d-%m-%Y')" \
    --mapname=5135${UNIQUEID} \
    --dem=$DEMPATH \
    --nsis \
    --product-id=${UNIQUEID} \
    --family-id=35 \
    --family-name="$MAPDESCRIPTION - OSM $(date +'%d-%m-%Y')" \
    --output-dir=output --bounds=$BOUNDS $DATA $TYPFILE

mv output/gmapsupp.img output/$OUTFILENAME

## Create Garming contours map

java -Xmx8g -jar $MKGMAPJAR -c $COPTIONS --style-file=$CSTYLEFILE \
    --description="$MAPDESCRIPTION - OSM - Contours $(date +'%d-%m-%Y')" \
    --series-name="$MAPDESCRIPTION - OSM - Contours $(date +'%d-%m-%Y')" \
    --mapname=5136${UNIQUEID} \
    --output-dir=output $CDATA $CTYPFILE

mv output/gmapsupp.img output/$COUTFILENAME

```
