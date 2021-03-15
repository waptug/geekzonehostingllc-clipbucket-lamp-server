# geekzonehostingllc-clipbucket-lamp-server
geekzonehostingllc/clipbucket-lamp-server

INSTRUCTIONS:

Requirements:

- docker
- docker-compose


1. Clone the git repository.

git clone https://github.com/waptug/geekzonehostingllc-clipbucket-lamp-server


2. Create a .env file by using the .env.sample as a template

cp .env.sample .env


3. Now edit the copied .env file and fill the information for MYSQL_PASSWORD and
MYSQL_ROOT_PASSWORD, don't touch anything else.


4. If you are in Linux, you will need to give permission to the following folders,
if your are on Windows make sure that the project is in a drive that is accesible by
docker.

For linux deployments:

chmod -R 777 app/upload/cache
chmod -R 777 app/upload/files
chmod -R 777 app/upload/includes
chmod -R 777 app/upload/images


5. Deploy the docker compose project.

docker-compose up -d

6. Open the browser and go to "localhost" if you are deploying locally or your
public ip address if you are on the cloud. You should be able to see the installation
wizard.

7. Keep in mind that in the wizard Precheck of modules. ffmpeg will appear as not
available, and thats fine. After the installation is finish it will recognized,
and tou check that bu going to the admin area-> Toolbox -> Server Modules Info.

8. In the database installation wizard make sure to fill out the information as follow:

host = mysql
database name = app
database user = admin
database password = (the one that is specified in the .env file)


9. Continue a finish the installation.



Additional notes:

- When you upload a video the application its going to process it,
so you will need to wait for that process to finish. Keep in mind
that speed of the process depends of your CPU Power. So try with
short videos first. You can check the process log in the admin area
Videos -> Videos Manager -> File convertion details .

- Also you can check the convertion queue in the
admin area -> toolbox -> Convertion queue manager.
