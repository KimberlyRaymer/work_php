import express from 'express'

import {
    getFile
} from './database.js'

const app = express()
app.use(express.json())
// app.set("view engine", "ejs")
const port = 8080

//tells express to use the files in the folder homepage
app.use(express.static("public"))

app.get('/', (req, res) => {
    // res.render("index.html")
    res.send("hello")
});

// /getFile/Wm82.a4.v1&Glyma%201.1
//get data from database.js
app.get("/getFile/:genomeInput&:genomeOutput", async (req, res) => {
        const param = req.params.genomeInput
        const param2 = req.params.genomeOutput
        const file = await getFile(param, param2)
        //send back to frontend
        res.send(file)
})

//error handling
app.use((err, req, res, next) => {
    console.error(err.stack)
    res.status(500).send('Something broke!')
})

app.listen(port, () => {
    console.log(`Server is running on port ${port}`)
})