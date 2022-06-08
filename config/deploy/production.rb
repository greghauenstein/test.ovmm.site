# Production Deployment
# ======================
set :user,              "[UPDATE_ME]"
set :stage,             :production
set :production_domain, fetch(:domain)
fetch(:default_env).merge!(wp_env: :production)

# Needs to be set in production if this is a Juicebox hosted site
fetch(:default_env).merge!(path: "/opt/cpanel/composer/bin:$PATH")

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, "/home/#{fetch(:user)}/sites/#{fetch(:production_domain)}/"

# Set temp directory since we're in a shared hosting environment
set :tmp_dir, "/home/#{fetch(:user)}/tmp"

server fetch(:production_domain),
  user: fetch(:user),
  roles: [:app, :web, :db]

# WP-CLI Configuration specifc to Production
if fetch(:is_production_ssl)
  set :wpcli_remote_url, "https://#{fetch(:production_domain)}"
else
  set :wpcli_remote_url, "http://#{fetch(:production_domain)}"
end
