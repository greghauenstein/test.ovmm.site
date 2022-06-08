# Staging Deployment
# ======================
set :user,            "staging"
set :stage,           :staging
set :staging_domain,  "#{fetch(:domain)}.#{fetch(:stage)}2.juiceboxint.com"
fetch(:default_env).merge!(wp_env: :staging)

# Needs to be set in production if this is a Juicebox hosted site
fetch(:default_env).merge!(path: "/opt/cpanel/composer/bin:$PATH")

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, "/home/#{fetch(:user)}/sites/#{fetch(:domain)}/"

# Set temp directory since we're in a shared hosting environment
set :tmp_dir, "/home/#{fetch(:user)}/tmp"

server fetch(:staging_domain),
  user: fetch(:user),
  roles: [:app, :web, :db]

# WP-CLI Configuration specifc to Staging
if fetch(:is_staging_ssl)
  set :wpcli_remote_url, "https://#{fetch(:staging_domain)}"
else
  set :wpcli_remote_url, "http://#{fetch(:staging_domain)}"
end
