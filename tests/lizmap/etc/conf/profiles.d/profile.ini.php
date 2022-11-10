[jdb]

default=jauth
jacl2_profile=jauth
lizlog=jauth

[jdb:jauth]
driver=pgsql
host=pgsql
port=5432
database=lizmap
user=lizmap
password="lizmap1234!"
search_path=public

[jdb:adresse]

driver=pgsql
host=pgsql
database=lizmap
user=lizmap
password="lizmap1234!"
search_path=adresse,public
timeout=10

