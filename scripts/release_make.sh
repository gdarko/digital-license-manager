#! /bin/bash
# A modification of Dean Clatworthy's deploy script at: https://github.com/deanc/wordpress-plugin-git-svn
# The difference is that this script lives in the plugin's git repo & doesn't require an existing SVN repo.

# default paths
SCRIPT_DIR="$( cd -- "$(dirname "$0")" >/dev/null 2>&1 ; pwd -P )"
PLUGIN_DIR="$( cd -- "$(dirname "$SCRIPT_DIR")" >/dev/null 2>&1 ; pwd -P )"
PLUGINS_ROOT_DIR="$( cd -- "$(dirname "$PLUGIN_DIR")" >/dev/null 2>&1 ; pwd -P )"
PLUGIN_SLUG=$(basename $PLUGIN_DIR)
MAINFILE="$PLUGIN_SLUG.php"

# svn config
SVNTMP="/tmp/$PLUGIN_SLUG-tmp"
SVNPATH="/tmp/$PLUGIN_SLUG"                             # path to a temp SVN repo. No trailing slash required and don't add trunk.
SVNURL="http://plugins.svn.wordpress.org/$PLUGIN_SLUG/" # Remote SVN repo on wordpress.org, with no trailing slash
rm -rf $SVNPATH
rm -rf $SVNTMP

# Let's begin...
echo ".........................................."
echo
echo "Preparing to deploy wordpress plugin"
echo
echo ".........................................."
echo

bash "$SCRIPT_DIR/release_prepare.sh"
if [[ ! -f "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip" ]]; then
  echo "Release archive not found. Exiting..."
  exit
fi

# Check version in readme.txt is the same as plugin file
NEWVERSION1=$(grep "^Stable tag:" $PLUGIN_DIR/readme.txt | awk '{print $NF}')
echo "readme version: $NEWVERSION1"
NEWVERSION2=$(grep "Version:" $PLUGIN_DIR/$MAINFILE | awk '{print $NF}')
echo "$MAINFILE version: $NEWVERSION2"

if [ "$NEWVERSION1" != "$NEWVERSION2" ]; then
  echo "Versions don't match. Exiting...."
  exit 1
else
  echo "Versions match in README.txt and PHP file. Let's proceed..."
fi

COMMITMSG="Version $NEWVERSION1"

cd $PLUGIN_DIR
if [ ! $(git tag -l "$NEWVERSION1") ]; then
  git commit -am "$COMMITMSG"

  echo "Tagging new version in git"
  git tag -a "$NEWVERSION1" -m "Tagging version $NEWVERSION1"

  echo "Pushing latest commit to origin, with tags"
  git push origin master
  git push origin "$NEWVERSION1"
else
  echo "Git tag already exists. Skipping"
fi

echo "Creating local copy of SVN repo ..."
if [ -d "$SVNPATH" ]; then
  rm -rf $SVNPATH
fi
svn co $SVNURL $SVNPATH

# If SVN tag does not exists, create it.
if [ ! -d "$SVNPATH/tags/$NEWVERSION1" ]; then

  echo "Changing directory to SVN and committing to trunk..."
  cd $SVNPATH/trunk/

  # re-construct PLUGIN_SLUG dir
  echo "Copying latest version to SVN trunk"
  unzip "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip" -d "$SVNTMP"
  rm -rf ./*
  cp -Rp "$SVNTMP/$PLUGIN_SLUG/"* ./
  rm -rf $SVNTMP

  # Update all the files that are not set to be ignored
  echo -e "Enter a SVN username: \c"
  read SVNUSER
  DELETED_FILES=$(svn status | grep -v "^.[ \t]*\..*" | grep "^\!" | awk '{print $2}')
  if [ ! -z "$DELETED_FILES" ]
  then
      echo $DELETED_FILES | xargs svn del
  fi
  UPDATED_FILES=$(svn status | grep -v "^.[ \t]*\..*" | grep "^?"  | awk '{print $2}')
  if [ ! -z "$UPDATED_FILES" ]
  then
      echo $UPDATED_FILES | xargs svn add
  fi

  # Commit the changes to svn repository
  svn commit --username=$SVNUSER -m "$COMMITMSG"

  echo "Creating new SVN tag & committing it"
  cd $SVNPATH

  # Copy and release new version
  svn copy trunk/ tags/$NEWVERSION1/
  cd $SVNPATH/tags/$NEWVERSION1
  svn commit --username=$SVNUSER -m "Tagging version $NEWVERSION1"

else
  echo "SVN tag already exists. Skipping"
fi

echo "Removing temporary directory $SVNPATH"
rm -fr $SVNPATH/
rm -fr "$PLUGINS_ROOT_DIR/$PLUGIN_SLUG.zip"

echo "New version $NEWVERSION1 published!"
