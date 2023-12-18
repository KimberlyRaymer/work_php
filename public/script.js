//------------------------------------------- Table Functions -------------------------------------------

/* There are multiple functions for createTable to accomodate for future changes
    such as larger tables, different button types, multiple button types, etc...
*/

//helper function for createRadioBttn to make label
function createLabel(optionName, buttonID) {
    let label;
    //create label so button can have text
    label = document.createElement("label");
    label.for = buttonID;
    label.innerHTML = optionName;
    
    return label;
}

//helper function for createTable to create buttons
function createRadioBttn(index, value, className, bttnName) {
    let radioBttn;
    //create button
    radioBttn = document.createElement("input");
    // radioBttn.required = true;
    radioBttn.className = className;
    radioBttn.type = "radio";
    radioBttn.id = className + (index + 1);
    radioBttn.name = bttnName;
    radioBttn.value = value;
    //radio buttons with same data col allows same functionality as attribute name (prevents user from clicking the same radio button option)
    radioBttn.dataset.col = (index + 1);

    return radioBttn;
}

//helper function for createTableRow to create table cell
function createTableCell(i, option, className, bttnName) {
    let td;
    td = document.createElement('td');

    //index of array is 0, this is the instruction provided
    if (i == 0) {
        td.innerHTML = option;
    }
    else {
        //create radio bttn with i as the index for the bttn
        let button = createRadioBttn(i, option, className, bttnName);
        //create label for radio bttn
        let label = createLabel(option, button.id);
    
        //create table cell
        td.appendChild(button);
        td.appendChild(label);
    }

    return td;
}

//helper function for createTable to create a table row with table cell
function createTableRow(i, option, className, bttnName) {
    var tr, td;
    //create table row
    tr = document.createElement('tr');

    td = createTableCell(i, option, className, bttnName);

    //append table cell to table row
    tr.appendChild(td);

    return tr;
}

//create tables dynamically
function createTable(tableID, optionArray, className, bttnName) {
    let table, tablerow;
    table = document.getElementById(tableID);

    //appends new made radio button(s) to table
    for (let i = 0; i < optionArray.length; i++) {
        tablerow = createTableRow(i, optionArray[i], className, bttnName);

        //add tablerow to table
        table.appendChild(tablerow);
    }

}

//------------------------------------------- End of Table Functions -------------------------------------------



//------------------------------------------- Start of List Parsing Functions ---------------------------------
// get genomes selected to app.js so app.js can sql query with those variables
async function getSQLGenomes(url) {
    return new Promise ((resolve, reject) => {
        fetch(url, {
            method : 'GET',
            headers : {
                'Content-Type' : 'application/json'
            },
        })
        .then(response => {
            resolve(response.text());
        })
        .catch((error) => {
            reject(error);
            console.log("Post Status: " + error);
        });
    })

}

//create url for get request
function getURL(genomeArray) {
    //get users chosen input/output genomes
    let genomeInput = genomeArray[0];
    let genomeOutput = genomeArray[1];
    //create url with input/output
    const url = `/getFile/${genomeInput}&${genomeOutput}`;
    return url;
}


// gets values from radio buttons
function getGenomes() {
    const selectArray = [];
    const genome = document.getElementsByClassName('genomeOptions');
    for (var i = 0; i < genome.length; i++) {
        if(genome[i].checked) {
            //add genomes to selectArray
            selectArray.push(genome[i].value);
        }
    }
    return selectArray;
}

//checks for duplications in userInputs
function checkDups(userInputs) {
    let genomes = [];
    userInputs.forEach(elem => {
        if (!genomes.includes(elem)) {
            genomes.push(elem);
        }
    })
    return genomes;
}

//gets textarea value if "Copy & Paste text" was chosen
function getTextAreaVal() {
    //parse text area
    const genomes = document.getElementById("genomeList").value.split('\n');
    //check for duplicates and then return unique inputs
    const userGenomes = [...checkDups(genomes)];
    //store userGenomes
    localStorage.setItem("userGenomes", userGenomes);
    return userGenomes;
}

// Modify textToArray to return a promise
function textToArray(text) {
    return new Promise((resolve) => {
        text = text.split('\n').map(line => line.trim());
        text = checkDups(text);
        console.log(text);
        resolve(text);
    });
}

// Modify getFileVal to return a promise
function getFileVals() {
    return new Promise((resolve, reject) => {
        const file = document.getElementById("genomeFile");

        // check if a file has been uploaded
        if (!file || !file.files || file.files.length === 0) {
            reject('Please select text a file.');
            return;
        }

        //user may upload multiple files, choose the first one
        const input = file.files[0];

        const reader = new FileReader();
        reader.onload = function (e) {
            // Resolve with the result of textToArray
            textToArray(e.target.result)
                .then((userGenomes) => resolve(userGenomes))
                .catch((error) => reject(error));
        };

        reader.onerror = function (e) {
            console.error('File reading error: ', e.target.error);
            reject(e.target.error);
        };

        reader.readAsText(input);
    });
}

//returns user genomes from a text file
async function getUserFileGenomes() {
    try {
        const userGenomes = await getFileVals();
        alert(userGenomes);
        return userGenomes.length !== 0 ? userGenomes : null;
    } catch (error) {
        console.error('Error reading file:', error);
        return null;
    }
}


//compares userlist with generated sql list
function compareLists(userGenomeArray, sqlList) {
    //holds output array
    const similog_names = [];

    //first row in csv file to indicate what column is what
    similog_names.push(['Submitted Feature', 'Similog Name']);
    // Iterates through user's submitted genomes
    for (var i = 0; i < userGenomeArray.length; i++) {
        var matches = [];
        //flag to check if the arrays have a match,
        //if not, then no matching elements will be paired with "no correspondence"
        var hasMatch = false;
        //look to find matches
        for (var j = 0; j < sqlList.length; j++) {
            // Checks to see if the names match
            if (userGenomeArray[i] === sqlList[j].feature_name) {
                matches.push(sqlList[j].similog_name);
                hasMatch = true;
            }
        }

        // check if element has match, if not pair with "no correspondence"
        // always push to similog_names array
        if (hasMatch) {
            var array = [userGenomeArray[i], matches.join(', ')];
            similog_names.push(array);
        } else {
            var array = [userGenomeArray[i], 'no correspondence'];
            similog_names.push(array);
        }
    }
    //checks if similog_names have any matches, if not return "no correspondence"
    return (similog_names.length != 0) ? similog_names : alert("no correspondence");
}

//------------------------------------------- End of List Parsing Functions ----------------------------------



// ------------------------------------------ Results Page Functions -----------------------------------------

//prints the matching similog names with what the user provided
function printResults(array) {
    var table = document.getElementById("rTable");
    // Iterate through the rows of the array
    for (var i = 0; i < array.length; i++) {
        // Create a table row
        var row = table.insertRow();

        // Iterate through the columns of the array
        for (var j = 0; j < array[i].length; j++) {
            // Create a cell for each value in the row
            var cell = row.insertCell();
            cell.textContent = array[i][j];
        }
    }
}

function arrayToCSV(array) {
    //get array value from 1D array generated by getSimilogNames and put into csv form
    const csvContent = "data:text/csv;charset=utf-8," + array.map(value => String(value)).join("\n");
    //return a csv link
    return encodeURI(csvContent);
}