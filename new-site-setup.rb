#!/usr/bin/env ruby
require 'erb'
require 'fileutils'
require 'json'
require 'securerandom'


## Cross-platform way of finding an executable in the $PATH.
# which('ruby') #=> /usr/bin/ruby
# h/t: https://stackoverflow.com/a/5471032
def which( cmd )
	exts = ENV['PATHEXT'] ? ENV['PATHEXT'].split(';') : ['']

  ENV['PATH'].split(File::PATH_SEPARATOR).each do |path|
    exts.each do |ext|
      exe = File.join(path, "#{cmd}#{ext}")
      return exe if File.executable?(exe) && !File.directory?(exe)
		end
	end

	# If nothing is found return nothing
  return nil
end


##
# Template to generate env files
##
class EnvTemplate
	include ERB::Util
	include SecureRandom
	attr_accessor :db_name, :db_user, :db_password, :domain, :environment

	def initialize( db_name, db_user, db_password, domain, environment )
		@db_name     = db_name
		@db_user     = db_user
		@db_password = db_password
		@domain      = domain
		@environment = environment
	end

  def render()
    ERB.new( File.read("templates/env.erb") ).result( binding )
  end

	def generate_salt( length = 64 )
		SecureRandom.base64( length )
	end

  def save(file)
    File.open(file, "w+") do |f|
      f.write(render)
    end
  end
end # end class EnvTemplate


##
# Template to generate htaccess files
##
class HtaccessTemplate
	include ERB::Util
	attr_accessor :is_network_site, :using_subdomains

	def initialize( is_network_site, using_subdomains )
		@is_network_site  = is_network_site
		@using_subdomains = using_subdomains
	end

  def render()
    ERB.new( File.read("templates/htaccess.erb") ).result( binding )
  end

  def save(file)
    File.open(file, "w+") do |f|
      f.write(render)
    end
  end
end # end class HtaccessTemplate


class DeployTemplate
	include ERB::Util
	attr_accessor :application, :is_network_site

	def initialize( application, is_network_site )
		@application     = application
		@is_network_site = is_network_site
	end

  def render()
    ERB.new( File.read( "templates/deploy.rb.erb" ) ).result( binding )
  end

  def save(file)
    File.open(file, "w+") do |f|
      f.write(render)
    end
  end
end # end class DeployTemplate

class StyleCssTemplate
	include ERB::Util
	attr_accessor :project_title, :project_description

	def initialize( project_title, project_description )
		@project_title       = project_title
		@project_description = project_description
	end

  def render()
    ERB.new( File.read( "templates/style.css.erb" ) ).result( binding )
  end

  def save(file)
    File.open(file, "w+") do |f|
      f.write(render)
    end
  end
end  # end class StyleCssTemplate

# Check that all of the required executables are present on the system,
# if not exit the script. Runs just before setup.
def check_executables
	%w(php composer ruby yarn wp).each do |executable|
		unless which(executable)
			puts "** #{executable} is required to run the script."
			exit
		end
	end
end


def setup
	puts "Hello! Thanks for using the OVMM Site Starter."
	puts "First we need to do some housekeeping...\n\n"

	# Making sure that bundler is installed and then run bundler to install the gems
	puts "Installing bundler..."
	`gem install bundler --no-document`

	puts "Bundling the gems..."
	`bundle`

	require 'highline'
	cli = HighLine.new
	cli.say "Ok, lets get started....\n\n"

	# Project Slug
	ask_project_slug = "Enter the 'slug' for the project (e.g. eastindia). This will be the project folder, theme name, database name and local URL. Use dashes, not underscores:"
	project_slug = cli.ask ask_project_slug

	# Project Title
	ask_project_title = "Enter new site title (e.g. East India Company):"
	project_title = cli.ask ask_project_title

	# Project URL
	# ask_project_url = "Enter new site url (e.g. eastindia.jb):"
	project_url = cli.ask( "Enter new site url (e.g. eastindia.jb), default:" ) do |q|
		q.default = "#{project_slug}.gh"
	end

	# DB Username
	db_username = cli.ask( "Enter the username for your database, default:" ) do |q|
		q.default = "root"
	end

	# DB Password
	db_password = cli.ask( "Enter the password for your database, default:" ) do |q|
		q.default = "123"
	end

	# Ask if we should create the database
	ask_create_db = cli.ask( "Do you want to create the database now? The user account will need create database permissions. [Y/N]" ) do |yn|
		yn.limit = 1, yn.validate = /[yn]/i
	end

	# Network Setup
	ask_is_network = cli.ask("\nIs this a network (multisite) site? [Y/N]") do |yn|
		yn.limit = 1, yn.validate = /[yn]/i
	end

	if ask_is_network.downcase == 'y'
		cli.say "\nCool! Network site!\n"
		is_network_site = true

		ask_if_subdomains = cli.ask("\nWill it be using subdomains? [Y/N]") do |yn|
			yn.limit = 1, yn.validate = /[yn]/i
		end

		if ask_if_subdomains == 'y'
			cli.say "OK, sudomains it is."
			using_subdomains = true
		else
			cli.say "Sub-directories are cool too."
			using_subdomains = false
		end

	else
		cli.say "\nNice...single site, keeping it simple.\n"
		is_network_site = false
	end


	###########################################################
	## Variables

	theme_dir                 = "web/app/themes"
	project_slug_underscored  = project_slug.gsub('-', '_')
	project_style_css         = "#{theme_dir}/#{project_slug}/style.css"
	project_database          = "#{project_slug_underscored}_wp"
	project_url               = project_url
	project_description       = "WordPress theme for #{project_title}"
	starter_project_name      = "ovmm-site-starter"
	wp_admin_user             = "ghadmin"
	wp_admin_pass             = "123"
	wp_admin_email            = "wp-admin@onevoice.agency"

	tmp_bedrock_dir						= "tmp-bedrock"

	@local_url      						= project_url
	@local_db_user  						= db_username
	@local_db_pass  						= db_password
	@local_database 						= project_database

	@development_url           = "[REPLACE_ME]"
	@development_db_user       = "[REPLACE_ME]"
	@development_db_pass       = "[REPLACE_ME]"
	@development_database      = "[REPLACE_ME]"

	@staging_url               = "[REPLACE_ME]"
	@staging_db_user           = "[REPLACE_ME]"
	@staging_db_pass           = "[REPLACE_ME]"
	@staging_database          = "[REPLACE_ME]"

	@production_url            = "[REPLACE_ME]"
	@production_db_user        = "[REPLACE_ME]"
	@production_db_pass        = "[REPLACE_ME]"
	@production_database       = "[REPLACE_ME]"


	# Private Composer Repositories
	# - Installed after bedrock is installed
	# - Assumed that these will all be privately Bitbucket hosted under
	# - the juiceboxint account.
	#
	# Composer install format:
	# composer config repositories.[plugin-name] vcs git@github.com:greghauenstein/[plugin-name].git
	private_composer_repositories = [
		'advanced-custom-fields-pro',
		'gravityforms',
		'gh-defaults',
	]

	# Composer Plugins
	#
	# Composer install format:
	# composer require [vendor]/[plugin-name]
	composer_plugins = [
		{ package: 'johngrogg/ics-parser' },
		{ package: 'league/csv' },
		{ package: 'stoutlogic/acf-builder' },
		{ package: 'timber/timber' },
		{ package: 'jjgrainger/posttypes' },
		{ package: 'wpackagist-plugin/rename-wp-login' },
		{ package: 'wpackagist-plugin/simple-page-ordering' },
		{ package: 'wpackagist-plugin/tinymce-advanced' },
		{ package: 'wpackagist-plugin/wp-mail-smtp' },
		{ package: 'greghauenstein/advanced-custom-fields-pro', version: 'dev-master' },
		{ package: 'greghauenstein/gravityforms', version: 'dev-master' },
		{ package: 'greghauenstein/gh-defaults', version: 'dev-master' },
	]

	# WordPress Deafult plugins - activated on install
	wp_default_plugins = [
		'advanced-custom-fields-pro',
		'gravityforms',
		'gh-defaults',
		'rename-wp-login',
		'simple-page-ordering',
	]

	###########################################################

	# Check that everything is ok
	cli.say "\nThis is everything so far:"
	cli.say "- Slug:            #{project_slug}"
	cli.say "- URL:             #{project_url}"
	cli.say "- Title:           #{project_title}"
	cli.say "- Description:     #{project_description}\n"
	cli.say "- Database:        #{project_database}\n"
	cli.say "- DB Username:     #{db_username}\n"
	cli.say "- DB Password:     #{db_password}\n"
	cli.say "- Create database: #{ ask_create_db == 'y' ? 'yes' : 'no' }\n"
	cli.say "- Network Site:    #{ is_network_site == true ? 'yes' : 'no' }\n"

	if is_network_site
		cli.say "- Network Type: #{ using_subdomains == true ? 'subdomains' : 'subdirectories' }\n"
	end

	confirm = cli.ask("\nDoes everything look correct? [Y/N]") do |yn|
		yn.limit = 1, yn.validate = /[yn]/i
	end

	if confirm.downcase == 'n'
		cli.say "\nOk, just run ./setup.rb again when you're ready"
		exit
	end

	## Cool
	cli.say "Cool!\n\n"


	## Database
	# - Create the database if we've been asked to
	if ask_create_db == 'y'
		cli.say "\n** Creating database... [#{project_database}]"
		`mysql -u#{db_username} -p#{db_password} -e 'CREATE DATABASE IF NOT EXISTS #{project_database}'`
	end


	## Bedrock
	# - Creates a project in a subdirectory and then the project files
	# - get moved to the root directory.
	cli.say "\n** Installing Bedrock into a temp folder..."
	`composer create-project roots/bedrock tmp-bedrock`

	# Remove the files from the temp directory we don't want in the root directory
	FileUtils.rm %w( tmp-bedrock/.gitignore tmp-bedrock/.env tmp-bedrock/.env.example )

	cli.say "\n** Moving Bedrock from temp folder to the root directory..."
	FileUtils.cp_r "tmp-bedrock/.", "./", verbose: true

	cli.say "\n** Removing temp Bedrock folder"
	FileUtils.remove_dir "tmp-bedrock"


	## Composer Config
	cli.say "\n** Updating composer config..."
	`composer config name greghauenstein/#{project_slug}`
	`composer config description "#{project_description}"`
	`composer config homepage "https://greghauenstein.com"`

	## Plugins
	# - First add the private repositories, then install each plugin
	cli.say "\n** Adding private composer repositories..."

	private_composer_repositories.each do |repository|
		cli.say "* Adding #{repository}"
		`composer config repositories.#{repository} vcs git@github.com:greghauenstein/#{repository}.git`
	end

	cli.say "\n** Installing plugins..."

	# Initalize the plugins array and loop through each plugin creating
	# an array to implode to pass onto composer to install.
	plugins = []

	# If this is a network site also install the network site url fixer
	if is_network_site
		composer_plugins << { package: 'roots/multisite-url-fixer' }
	end

	composer_plugins.each do |plugin|
		plugin_string = plugin[:package]
		plugin_string << ":#{plugin[:version]}" unless plugin[:version].nil?

		plugins << plugin_string
	end

	cli.say "* #{plugins.join(' ')}"
	`composer require #{plugins.join(' ')}`

	## Theme
	# - Rename theme for the project
	cli.say "\n** Renaming theme..."
	FileUtils.mv( "web/app/themes/#{starter_project_name}", "web/app/themes/#{project_slug}" )

	## Creating the style.css
	cli.say "\n** Creating theme style.css..."
	style_css_file = "web/app/themes/#{project_slug}/style.css"

	# Delete the htaccess file if it exists
	if File.exist?( style_css_file )
		File.delete( style_css_file )
	end

	style_css = StyleCssTemplate.new( project_title, project_description )
	style_css.save( style_css_file )


	## ENV Environments
	# - Read in the default env file and create an env file for each environment
	cli.say "\n** Creating environment files..."

	# Loop through each environment type, using the EnvTemplate class
	# generate a env file for each environment
	%w( local development staging production ).each do |environment|

		# In the local environment use only `.env` and the dev environment
		# is actually development too, not local.
		if environment.eql?("local")
			env_filename    = '.env'
			env_environment = 'development'
		else
			env_filename    = ".env.#{environment}"
			env_environment = environment
		end

		env = EnvTemplate.new(
			instance_variable_get("@#{environment}_database"),
			instance_variable_get("@#{environment}_db_user"),
			instance_variable_get("@#{environment}_db_pass"),
			instance_variable_get("@#{environment}_url"),
			env_environment
		)

		env.save( env_filename )
	end


	## htaccess
	# - Setup the htaccess for single or network sites with
	# - subdomains or subdirectory
	cli.say "\n** Creating htaccess file..."
	htaccess_file = "web/.htaccess"

	# Delete the htaccess file if it exists
	if File.exist?( htaccess_file )
		File.delete( htaccess_file )
	end

	htaccess = HtaccessTemplate.new( is_network_site, using_subdomains )
	htaccess.save( htaccess_file )


	## Capistrano Deploy Config
	# - Setup the capistrano deploy config, only variable needed is `application`
	# - which is the `project_slug`
	cli.say "\n** Creating config/deploy.rb file..."
	cap_deploy_file = "config/deploy.rb"

	cap_deploy = DeployTemplate.new( project_slug, is_network_site )
	cap_deploy.save( cap_deploy_file )


	## Bedrock Config Customization
	# - Adds a WP_CONTENT_DIR used in uploading files. This possibly could be
	# - removed in the future. Currently used in DBP to get the base content directory.
	# - Also adds network settings if this is a network site.
	application_config_file = "config/application.php"
	application_config_tmp  = ""

	# Loop through each line of the application_config, adding each line to the
	# tmp file. If the specific lines we are looking for are found add them to
	# config file.
	if File.exists?( application_config_file )
		File.foreach( application_config_file ) do |line|
			application_config_tmp << line

			if line.include?( "Config::define('CONTENT_DIR', '/app');" )
				application_config_tmp << "Config::define('WP_WEBROOT_DIR', $webroot_dir);\n"
			end

			if is_network_site and line.include?( "ini_set('display_errors', '0');" )
				multisite_settings = %Q(

/**
 * Multisite Settings
 */
Config::define( 'WP_ALLOW_MULTISITE',   true );
Config::define( 'MULTISITE',            true );
Config::define( 'SUBDOMAIN_INSTALL',    #{ using_subdomains == true ? 'true' : 'false' } );
Config::define( 'DOMAIN_CURRENT_SITE',  env('DOMAIN_CURRENT_SITE') ? : WP_HOME );
Config::define( 'PATH_CURRENT_SITE',    env('PATH_CURRENT_SITE') ? : '/' );
Config::define( 'SITE_ID_CURRENT_SITE', env('SITE_ID_CURRENT_SITE') ?: 1 );
Config::define( 'BLOG_ID_CURRENT_SITE', env('BLOG_ID_CURRENT_SITE') ?: 1 );

				)

				application_config_tmp << multisite_settings
			end
		end # end foreach application_config

		# Check that the temp application config isn't empty,
		# delete the existing application config and write the file
		unless application_config_tmp.empty?
			File.delete( application_config_file )
			File.write( application_config_file, application_config_tmp )
		end

	end # end if application_config_file exists

	# Add WP_DEBUG_LOG to the development config so that debug logs
	# are written to the web/app/debug.log instead of the default
	# PHP system log.
	development_config_file = "config/environments/development.php"
	development_config_tmp  = ""

	if File.exists?( development_config_file )
		File.foreach( development_config_file ) do |line|
			development_config_tmp << line

			if line.include?( "Config::define('WP_DEBUG_DISPLAY', true);" )
				development_config_tmp << "Config::define('WP_DEBUG_LOG', true);\n"
			end

		end # end foreach development_config

		# Check that the temp development config isn't empty,
		# delete the existing development config and write the file
		unless development_config_tmp.empty?
			File.delete( development_config_file )
			File.write( development_config_file, development_config_tmp )
		end

	end # end if development_config_file exists


	# Production Config - copy the staging environment
	staging_config_file    = "config/environments/staging.php"
	production_config_file = "config/environments/production.php"
	production_config_tmp  = ""

	if File.exists?( staging_config_file ) and !File.exists?( production_config_file )
		File.foreach( staging_config_file ) do |line|

			if line.include?( "* Configuration overrides for WP_ENV === 'staging'" )
				production_config_tmp << " * Configuration overrides for WP_ENV === 'production'\n"
			else
				production_config_tmp << line
			end
		end # end foreach development_config

		# Check that the temp production config isn't empty, in case there
		# is an existing production file present don't overwrite it.
		unless development_config_tmp.empty?
			File.write( production_config_file, production_config_tmp )
		end
	end # end if staging_config_file exists


	## WordPress Setup
	# - Vary the type of install depending if this is a network install
	# - with or without subdomains
	cli.say "\n** Setting up WordPress..."

	if is_network_site
		wp_cli_install = "wp core multisite-install"
		wp_cli_install << " --subdomains" if using_subdomains
	else
		wp_cli_install = "wp core install"
	end

	wp_cli_install << " --url=#{project_url}"
	wp_cli_install << " --title='#{project_title}'"
	wp_cli_install << " --admin_user=#{wp_admin_user}"
	wp_cli_install << " --admin_password=#{wp_admin_pass}"
	wp_cli_install << " --admin_email=#{wp_admin_email}"

	cli.say "\n* Setting up WordPress with..."
	cli.say "#{wp_cli_install}"
	`#{wp_cli_install}`

	cli.say "\n* Activating default plugins... #{wp_default_plugins.join(" ")}"
	`wp plugin activate #{is_network_site ? '--network' : ''} #{wp_default_plugins.join(" ")}`

	# Deactivate juicebox-defaults since it only needs to run once
	`wp plugin deactivate #{is_network_site ? '--network' : ''} juicebox-defaults`

	cli.say "\n* Setting custom login page url (/jb-login)..."
	`wp option add rwl_page jb-login --autoload=yes`

	cli.say "\n* Activating the theme [#{project_slug}] ..."
	`wp theme activate #{project_slug}`

	## Git Repo
	# -  Delete the original repo, check that it exists first
	git_repo = ".git"

	if Dir.exist?( git_repo )
		cli.say "\n* Removing original git repo and setting up a new one ..."
		FileUtils.remove_dir( git_repo )
	end

	cli.say "\n* Setting up git repo ..."
	`git init`
	`git add .`
	`git commit -m "Initial project commit"`


	## Node Modules
	# - Install the node_modules with yarn
	cli.say "\n** Installing 94,823 billion node_modules..."
	`yarn install`

	cli.say "\n* Compiling assets for the first time..."
	`yarn prod`


	## All Done!
	cli.say "\n\n** All done!"
	cli.say "Check http://#{project_url} for your new site"
	cli.say "Use yarn dev to monitor assets with browserfiy."

end # end def setup



## Start the setup script after checking the required executables exist
check_executables()
setup()



# class TemplateGenerator
# 	attr_reader :template, :rendered_file, :binding_klass

# 	def initialize(binding_klass)
# 		@template      = File.open( template, 'rb', &:read )
# 		@rendered_file = rendered_file
# 		@binding_klass = binding_klass
# 	end

# 	def render
# 		ERB.new( File.read( template ) ).result( binding_klass.get_binding )
# 	end

# 	def save( file )
# 		File.open( file, "w+" ) do |f|
# 			f.write( render )
# 		end
# 	end
# end # end class TemplateGenerator
