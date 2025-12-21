#!/usr/bin/env sh

gitPath="../.git/hooks"

if [ -n "$(composer show | grep 'captainhook/captainhook')" ];
then
  vendor/bin/captainhook install -f --only-enabled --run-mode=docker --run-exec="docker compose run --rm php-cli" --run-path="vendor/bin/captainhook" > /dev/null 2>&1;

  if [ ! -d "$gitPath" ]; then
      echo "Директория $gitPath не существует."
      exit 1
  fi

  for file in "$gitPath"/*; do
      if grep -q -- '--configuration='"'backend\/captainhook.json'"'' "$file"; then
          sed -i 's/--configuration='"'backend\/captainhook.json'"'/--configuration='"'captainhook.json'"'/g' "$file"

          echo "Изменения в файле $file выполнены."
      fi
  done
fi
