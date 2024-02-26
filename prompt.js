// Prompt the user for their name
let name = prompt("What is your name?");

// Check if the user provided a name
if (name) {
    // Greet the user with their name
    console.log("Hello, " + name + "! Welcome to the world of JavaScript!");
} else {
    // If the user didn't provide a name, greet them without it
    console.log("Hello! Welcome to the world of JavaScript!");
}
