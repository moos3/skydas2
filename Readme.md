### Installation Instructions
You will need the following packages installed

- RabbitMq

```
brew install rabbitmq
```

- nodejs install

```
cd js
npm install rabbit.js
npm install socket.io
```

### Generating Test data
Open terminal to a new tab / window

run `php update.php`

this will push data into rabbitmq

### Running the Web sockets server
Open terminal to a new tab / window

```
cd js
node server.js
```

### Viewing the data
bring up index.html in your browser. Data is randomly generated.
