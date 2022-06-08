# Development Deployment
# ======================
set :user,                "juicebox"
set :stage,               :development
set :development_domain,  "#{fetch(:domain)}.dev.juiceboxint.com"
fetch(:default_env).merge!(wp_env: :development)

# Needs to be set in production if this is a Juicebox hosted site
fetch(:default_env).merge!(path: "/opt/cpanel/composer/bin:$PATH")

# Default deploy_to directory is /var/www/my_app_name
set :deploy_to, "/home/#{fetch(:user)}/public_html/#{fetch(:domain)}/"

# Set temp directory since we're in a shared hosting environment
set :tmp_dir, "/home/#{fetch(:user)}/tmp"

server fetch(:development_domain),
  user: fetch(:user),
  roles: [:app, :web, :db]

# WP-CLI Configuration specifc to Development
if fetch(:is_development_ssl)
  set :wpcli_remote_url, "https://#{fetch(:development_domain)}"
else
  set :wpcli_remote_url, "http://#{fetch(:development_domain)}"
end
