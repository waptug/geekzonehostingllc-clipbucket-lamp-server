# geekzonehostingllc-clipbucket-lamp-server
geekzonehostingllc/clipbucket-lamp-server
See docker hub repo at docker pull geekzonehostingllc/clipbucket-lamp-server: latest

Enjoy a self hosted Clipbucket.Com Video Streaming Server and photo gallery that you control and maintain.

This is a fully built out LAMP stack server running linux with all dependencies like ffmpeg and everything needed with a fully built and configured Default Clipbucket.Com Free Version install. It is ready for you to add content to and style the theme as you like.

Included with the Enhanced Clipbucket image is the phpMyAdmin script for you to edit your database if needed. The system also has a automatic CertBot service running so you can register your SSL cert using Let's Encrypt and the CertBot will automaticly renew your cert for you.

Building a working Clipbucket server from scratch is a very difficult process to install all of the server dependencies required. This image has done all the hard work for you. This image was produced by Michael Scott McGinn at GeekZoneHosting.Com and contributed to the Docker community.

INSTRUCTIONS:

Requirements: You MUST have the following installed and running on your server.

Install Docker https://docs.docker.com/engine/install/ubuntu/     
and this for docker compose:  https://docs.docker.com/compose/install/

    docker
    docker-compose
    git

=========================================================

DO NOT BEGIN UNTIL THE PREVIOUS STEPS HAVE BEEN COMPLETED.

=========================================================

From the Linux command line - Start by Creating a folder on your server to hold this project and run step 1 from that folder

    Clone the git repository.

git clone https://github.com/waptug/geekzonehostingllc-clipbucket-lamp-server

    Create a .env file by using the .env.sample as a template

cp .env.sample .env

    Now edit the copied .env file and fill the information for MYSQL_PASSWORD and MYSQL_ROOT_PASSWORD, don't touch anything else.

    If you are in Linux, you will need to give permission to the following folders, if your are on Windows make sure that the project is in a drive that is accessible by docker.

For linux deployments:

chmod -R 777 app/upload/cache

chmod -R 777 app/upload/files

chmod -R 777 app/upload/includes

chmod -R 777 app/upload/images

    Deploy the docker compose project.

docker-compose up -d

    Open the browser and go to "localhost" if you are deploying locally or your public ip address if you are on the cloud. You should be able to see the installation wizard.

    Keep in mind that in the wizard Precheck of modules. ffmpeg will appear as not available, and that is fine. After the installation is finished it will by recognized, and to check that buy going to the admin area-> Toolbox -> Server Modules Info.

    In the database installation wizard make sure to fill out the information as follow:

host = mysql database name = app database user = admin database password = (the one that is specified in the .env file)

    Continue and finish the installation.

Additional notes:

    When you upload a video the application its going to process it, so you will need to wait for that process to finish. Keep in mind that speed of the process depends of your CPU Power. So try with short videos first. You can check the process log in the admin area Videos -> Videos Manager -> File conversion details .

    Also you can check the conversion queue in the admin area -> toolbox -> Conversion queue manager.

====================================================

FINISHED YOU ARE DONE - Enjoy your Clipbucket Docker Image

====================================================

After you get this image installed if you need something customized about the docker image or new modifications to the docker image you can purchase services from this affiliate link.

https://fvrr.co/3vE1B2F

GeekZoneHosting.Com, LLC will receive a commission if you purchase any services from clicking the above link.

GeekZoneHosting.Com appreciates your support of this project. If you appreciate this service and would like to directly support GeekZoneHosting.Com please consider purchasing a VPS server from us or a domain name for your site. https://geekzonehosting.com

We can sell Virtual Private Server hosting and dedicated managed Linux servers to you if you do not have your own server solution.

See the working install of this image at https://videoserver.summerstreams.com for reference.

Community Support for using Clipbucket is available at Clipbucket.com
