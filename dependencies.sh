#!/bin/bash

{
  echo "# Dependencies"
  echo ""
  echo "## PHP (Composer) Dependencies"
  echo ""
  echo "| Package | License |"
  echo "|---------|---------|"
  composer licenses | tail -n +2 | awk '{
    license = "";
      for (i = 3; i <= NF; ++i) {
        license = license $i " ";
      }
      gsub(/[[:space:]]+$/, "", license);
      if (license != "" && license != "License") {
        printf("| %s@%s | %s |\n", $1, $2, license);
      }
    }'
} > DEPENDENCIES.md

{
  echo ""; 
  echo "## Node (npm) Dependencies"; 
  echo ""; 
  echo "| Package | License |"; 
  echo "|---------|---------|"; 
  npx license-checker --json --production | node -e "
    const data = Object.entries(JSON.parse(require('fs').readFileSync(0, 'utf-8')));
    data.forEach(([pkg, info]) => {
      const license = info.licenses || 'N/A';
      console.log(\`| \${pkg} | \${license} |\`);
    });
  "
} >> DEPENDENCIES.md
