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
function createRadioBttn(index, name) {
    let radioBttn;
    //create button
    radioBttn = document.createElement("input");
    radioBttn.type = "radio";
    radioBttn.id = "formatOptions" + (index + 1);
    radioBttn.name = name;
    //radio buttons with same col allows same functionality as attribute name
    radioBttn.dataset.col = (index + 1);

    return radioBttn;
}

//helper function for createTableRow to create table cell
function createTableCell(i, option, name) {
    let td;
    td = document.createElement('td');

    if (i == 0) {
        td.innerHTML = option;
    }
    else {
        //create radio bttn with i as the index for the bttn
        let button = createRadioBttn(i, name);
        //create label for radio bttn
        let label = createLabel(option, button.id);
    
        //create table cell
        td.appendChild(button);
        td.appendChild(label);
    }

    return td;
}

//helper function for createTable to create a table row with table cell
function createTableRow(i, option, name) {
    var tr, td;
    //create table row
    tr = document.createElement('tr');

    td = createTableCell(i, option, name);

    //append table cell to table row
    tr.appendChild(td);

    return tr;
}

//create tables dynamically
function createTable(tableID, optionArray, name) {
    let table, tablerow;
    table = document.getElementById(tableID);

    //appends new made radio button(s) to table
    for (let i = 0; i < optionArray.length; i++) {

        tablerow = createTableRow(i, optionArray[i], name);

        //add tablerow to table
        table.appendChild(tablerow);
    }

}

//------------------------------------------- End of Table Functions -------------------------------------------
