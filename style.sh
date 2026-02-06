script='<script src="/js/ads.js"></script>'

for f in index.php detail_post.php; do
  grep -Fq "$script" "$f" && continue

  if grep -qi '</body>' "$f"; then
    perl -0777 -i -pe "s#</body>#$script\\n</body>#i" "$f"
  else
    printf "\n%s\n" "$script" >> "$f"
  fi
done
