## What's the point? 

I've always preferred using direct command line clients for database access over web interfaces such as phpmyadmin.

In order to optimize that experience, I'd gotten accustomed to using grcat and a custom terminal profile to colorize the output.

While this gave me the functionality I desire, it added a complexity in setting up a development environment as I needed to ensure the mysql client and grcat were available on the base machine and pull in custom terminal and grcat configurations. 

This package started as an experiment in capturing that functionality directly within the codebase, and eliminate those hurdles.

After using it for a few weeks, It felt to me that it successfully captures the interface. 
 
Further, this package as a dev-dependency per project is cleaner and simpler than the environmental requirements I previously worked with. 