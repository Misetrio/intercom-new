
Vagrant.configure("2") do |config|

  config.vm.box = "ubuntu/xenial64"
  config.vm.network "forwarded_port", guest: 80, host: 1234, host_ip: "127.0.0.1"

  config.vm.provider "virtualbox" do |vb|
    vb.memory = "512"
  end

  # folders to mount
  config.vm.synced_folder "./", "/var/www/html"

  config.vm.provision "shell" do |s|
      s.path = ".vagrant/provision.sh"
  end

end