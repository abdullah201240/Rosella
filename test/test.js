"use strict";



const { execSync } = require("child_process");
const fs = require("fs");

const FILE_NAME = "auto_commit.txt";
const TOTAL_COMMITS = 1000;

for (let i = 1; i <= TOTAL_COMMITS; i++) {
  // Modify file to ensure change
  fs.writeFileSync(FILE_NAME, `Commit number: ${i} at ${new Date().toISOString()}`);

  try {
    // Run Git commands
    execSync("git add .");
    execSync(`git commit -m "#${i}"`);
    execSync("git push origin main"); // Change 'main' to your branch name if different

    console.log(`Commit #${i} pushed successfully.`);
  } catch (error) {
    console.error(`Error on commit #${i}:`, error.message);
    break;
  }
}

console.log("✅ Auto commit process completed!");

