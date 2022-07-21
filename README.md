## Starting development

1. Rebuild the autoload map


    ./composer.sh dump-autoload

## Deploying to wordpress.org

1. Push to github (into the main branch)

    git push github master:main

2. Create a new release (with the release number matching the version number). This will automatically 
   deploy the plugin to wordpress.org's Subversion repository.
