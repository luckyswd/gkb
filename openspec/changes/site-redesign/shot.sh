#!/bin/bash
# Снимок страницы на заданной ширине.
# У headless Chrome минимальная ширина окна 500px, поэтому узкие ширины
# снимаются через центрированный iframe в окне 500px с последующей обрезкой.
# Использование: shot.sh <выходной каталог> <имя> <url> <ширина> <высота>
set -u
SCRATCH="/private/tmp/claude-501/-Users-denis--claude-spares-spare-q6cJ0k/f9da1e1e-5936-42f6-a3dd-8e26ad5ece56/scratchpad"
CHROME="/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"
OUT=$1; NAME=$2; URL=$3; W=$4; H=$5
TMP="$SCRATCH/shots-raw"
# профиль чистится перед каждым снимком: иначе Chrome отдаёт CSS из
# своего HTTP-кеша и на снимке оказывается прошлая версия стилей
rm -rf "$SCRATCH/chrome-profile"
mkdir -p "$OUT" "$TMP" "$SCRATCH/chrome-profile"
RAW="$TMP/$NAME-$W.png"

run() { "$CHROME" --headless=new --disable-gpu --no-first-run --hide-scrollbars \
  --user-data-dir="$SCRATCH/chrome-profile" --virtual-time-budget=15000 \
  --window-size="$1,$2" --screenshot="$RAW" "$3" >/dev/null 2>&1; }

if [ "$W" -lt 500 ]; then
  WRAP="$TMP/wrap-$NAME-$W.html"
  cat > "$WRAP" <<EOF
<!doctype html><html><head><meta charset="utf-8">
<style>html,body{margin:0;padding:0;background:#fff}
iframe{display:block;width:${W}px;height:${H}px;border:0;margin:0 auto}</style></head>
<body><iframe src="$URL" scrolling="no"></iframe></body></html>
EOF
  run 500 "$H" "file://$WRAP"
  [ -s "$RAW" ] && sips -c "$H" "$W" "$RAW" >/dev/null 2>&1
else
  run "$W" "$H" "$URL"
fi

if [ -s "$RAW" ]; then
  sips -s format jpeg -s formatOptions 75 "$RAW" --out "$OUT/$NAME-$W.jpg" >/dev/null 2>&1
  printf '%-14s %5s  %s  %s\n' "$NAME" "$W" "$(sips -g pixelWidth "$OUT/$NAME-$W.jpg" | awk '/pixelWidth/{print $2}')px" "$(du -h "$OUT/$NAME-$W.jpg" | cut -f1)"
else
  printf '%-14s %5s  ОШИБКА\n' "$NAME" "$W"
fi
