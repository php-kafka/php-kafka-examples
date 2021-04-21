# Redpanda playground
[Redpanda](https://vectorized.io/redpanda) support all parts of the Kafka API except for the transactions API.  
You can find the issue in their public github [here](https://github.com/vectorizedio/redpanda/issues/445).  
This means the currently included transaction examples will not work atm.  

## Running examples
1. `docker-compose up -d`
2. `docker-compose exec php composer update`
3. Check the subfolders on how to run the examples