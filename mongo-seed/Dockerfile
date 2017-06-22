FROM mongo

COPY init.json /init.json
CMD mongoimport --host mongodb --db test --collection people --type json --file /init.json --jsonArray
