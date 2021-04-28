# geekzonehostingllc-clipbucket-lamp-server
geekzonehostingllc/clipbucket-lamp-server
See docker hub repo at docker pull geekzonehostingllc/clipbucket-lamp-server: latest

Enjoy a self hosted Clipbucket.Com Video Streaming Server and photo gallery that you control and maintain.

This is a fully built out LAMP stack server running linux with all dependencies like ffmpeg and everything 
needed with a fully built and configured Default Clipbucket.Com Free Version install. It is ready for you to add 
content to and style the theme as you like.

Included with the Enhanced Clipbucket image is the phpMyAdmin script for you to edit your database if needed. The 
system also has a automatic CertBot service running so you can register your SSL cert using Let's Encrypt and the 
CertBot will automaticly renew your cert for you.

Building a working Clipbucket server from scratch is a very difficult process to install all of the server
dependencies required. This image has done all the hard work for you. This image was produced by 
Michael Scott McGinn at GeekZoneHosting.Com and contributed to the Docker community.


INSTRUCTIONS:
This system requires that the server you are installing this on is running Ubuntu Linux version 20 or better.

Things to decide: Are you going to install a SSL certificate on your site? Then do that first before you install Clipbucket.

Are you going to make your site public and point a domain name to it? 

Then you will need to have your domain name A record pointing to your server IP address and have given it time to propagate after you have changed it. 

Usually 6 to 72 hours it will take before folks will be able to see your site.

If you need a domain name please consider https://geekzonehosting.com or https://mtbn.net and register one.

You will also need to set up a port forwarding on your router to allow traffic to port 80 or which ever port you
want to run this on of the server you are running on if you are running this on your own bearmetal server in your home or office.

If you are just going to run your site on a local server on your intranet and access it via localhost or an ip address then you 
do not need a domain name registered.

Requirements: You MUST have the following installed and running on your server unless you are running a vps with Linux ubuntu already installed.

Setup your Computer to be able to run as a web server.

1.) Install Ubuntu Desktop or Server- Your choice. 
https://ubuntu.com/download

After you have tested that you can boot your computer and login to the system then continue.

Open a comand terminal from the desktop if you are running the desktop graphical user interface.

Make sure you are working as the super user on the system

Type this at your comand prompt

sudo su

Open a browser window if your running in desktop mode and read up on how to install docker and docker-compose. 
If your running in server mode without a graphical desktop you will need another computer running a browser or you will need
to install and use a text browser like lynx to access the following web sites.

To install lynx if you need it:  apt install lynx

2.) Install Docker https://docs.docker.com/engine/install/ubuntu/     
3.) Install Docker-Compose https://docs.docker.com/compose/install/
4.) Test if you have git working. Git should already be install by default.
     
     Type:  git --version 
     It should report the version of git you are running or give you an error and suggest how to install git if it is not installed.
     
5.) Install net-tools so you can use the ifconfig command.
    apt install net-tools
    
    Then type 
    
      ifconfig
      
      to see what the ip address of your server is
      
      You will need this info to set up your system 
      You will also need to know the public ip address pointing to your network 
      In a browser using https://whatismyip.com to see what your isp has assigned to you this will be the ip address you 
      point your domain to or what address you access your site from.
      It is recommended to have a static ip address from your isp but you can also use a dynamic ip (just know that you may not
      depend on the dynamic ip address to stay the same if your isp changes it on you.) You may need to setup a dynamic
      ip fordering system on your router.
      
=========================================================

DO NOT BEGIN UNTIL THE PREVIOUS STEPS HAVE BEEN COMPLETED.

=========================================================


1.) Clone the git repository. It does not matter what folder your in when you begin.

    git clone https://github.com/waptug/geekzonehostingllc-clipbucket-lamp-server

        Create a .env file by using the .env.sample as a template

    cp .env.sample .env

        Now edit the copied .env file and fill the information for MYSQL_PASSWORD and MYSQL_ROOT_PASSWORD, 
        don't touch anything else.

        If you are in Linux, you will need to give permission to the following folders, if your are on 
        Windows make sure that the project is in a drive that is accessible by docker.

    For linux deployments:

    chmod -R 777 app/upload/cache

    chmod -R 777 app/upload/files

    chmod -R 777 app/upload/includes

    chmod -R 777 app/upload/images


There are to ways to deploy this project. The first option is more suitable
for deploying the application locally. And the second option it's for
SSL support with your own domain.You must have a domain name to use SSL.


Option  #1 - Local deployment (without SSL)


    Deploy the docker compose project.

    docker-compose up -d

        Open the browser and go to "localhost" if you are deploying locally or your public ip address 
        if you are on the cloud. 
        
        You should be able to see the installation wizard.

        Keep in mind that in the wizard Precheck of modules. ffmpeg will appear as not available, and that 
        is fine. After the installation is finished it will be recognized, and to check that by going to 
        the admin area-> Toolbox -> Server Modules Info.

        In the database installation wizard make sure to fill out the information as follow:

    host = mysql 
    database name = app 
    database user = admin 
    database password = (the one that is specified in the .env file)

        Continue and finish the installation.


Option #2 - Deployment with SSL

     (Make sure your domain is pointing to your server IP address before you proceed)

     You can check the SSL how to video here at https://videoserver.summerstreams.com

    - Edit init-letsencrypt.sh file and replace yourdomain.com for your actual domain.
    - Edit docker/vhosts/default.conf and replace yourdomain.com for your actual domain.
    - Uncomment (remove #) from line 17 of the docker-compose.yml, that will allow the 
      apache server to ready for the certificate request.

        #- ./docker/vhosts:/etc/apache2/sites-enabled

    - Execute the init-letsencrypt.sh  
    
        (you should receive a congratulations message at the end of the execution)
        
    - Put down docker services: docker-compose down
    - Now lets create the SSL configuration for this certificate.

        cp docker/vhosts/sample.ssl.txt cp docker/vhosts/yourdomain.com.conf

        (Make sure to end the file with a .conf extension)

        Edit the file and replace the 3 ocurrencies of yourdomain.com for your actual
        domain.

    - Bring the services back up: docker-compose up -d

        (you should be able to go to the browser and go to your domain )


    Keep in mind that in the wizard Precheck of modules. ffmpeg will appear as not available, 
    and that is fine. After the installation is finished it will by recognized, and to check 
    that buy going to the  admin area-> Toolbox -> Server Modules Info.

    In the database installation wizard make sure to fill out the information as follow:

    host = mysql 
    database name = app 
    database user = admin 
    database password = (the one that is specified in the .env file)

    Continue and finish the installation.


Additional notes:

    When you upload a video the application its going to process it, so you will need to wait for
    that process to finish. Keep in mind that speed of the process depends of your CPU Power. So try 
    with short videos first. You can check the process log in the admin area 
    Videos -> Videos Manager -> File conversion details .

    Also you can check the conversion queue in the admin area -> toolbox -> Conversion queue manager.

====================================================

FINISHED YOU ARE DONE - Enjoy your Clipbucket Docker Image

====================================================

After you get this image installed if you need something customized about the docker image or new 
modifications to the docker image you can purchase services from this affiliate link.

https://fvrr.co/3vE1B2F

GeekZoneHosting.Com, LLC will receive a commission if you purchase any services from clicking 
the above link.

GeekZoneHosting.Com appreciates your support of this project. If you appreciate this service and would 
like to directly support GeekZoneHosting.Com please consider purchasing a VPS server from us or a domain 
name for your site. https://geekzonehosting.com

We can sell Virtual Private Server hosting and dedicated managed Linux servers to you if you do not have 
your own server solution. 

See the working install of this image at https://videoserver.summerstreams.com for reference.

Community Support for using Clipbucket is available at Clipbucket.com
