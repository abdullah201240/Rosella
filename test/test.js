"use strict";



// const { execSync } = require("child_process");
// const fs = require("fs");

// const FILE_NAME = "auto_commit.txt";
// const TOTAL_COMMITS = 1000;

// for (let i = 1; i <= TOTAL_COMMITS; i++) {
//   // Modify file to ensure change
//   fs.writeFileSync(FILE_NAME, `Commit number: ${i} at ${new Date().toISOString()}`);

//   try {
//     // Run Git commands
//     execSync("git add .");
//     execSync(`git commit -m "#${i}"`);
//     execSync("git push origin main"); // Change 'main' to your branch name if different

//     console.log(`Commit #${i} pushed successfully.`);
//   } catch (error) {
//     console.error(`Error on commit #${i}:`, error.message);
//     break;
//   }
// }

// console.log("✅ Auto commit process completed!");

// const { execSync } = require("child_process");
// const fs = require("fs");

// const FILE_NAME = "auto_commit.txt";
// const TOTAL_COMMITS = 30; // One commit per day in June 2024

// for (let i = 1; i <= TOTAL_COMMITS; i++) {
//   const commitDate = new Date(`2024-06-${String(i).padStart(2, "0")}T12:00:00`);

//   // Modify file to ensure change
//   fs.writeFileSync(FILE_NAME, `Commit on ${commitDate.toISOString()}`);

//   try {
//     // Set environment variables for commit date
//     const gitEnv = `GIT_COMMITTER_DATE="${commitDate.toISOString()}" GIT_AUTHOR_DATE="${commitDate.toISOString()}"`;

//     // Run Git commands
//     execSync("git add .");
//     execSync(`${gitEnv} git commit -m "Commit for June ${i}, 2024"`, { stdio: "inherit" });
//     execSync("git push origin main"); // Change 'main' to your branch if different

//     console.log(`Commit for June ${i}, 2024 pushed successfully.`);
//   } catch (error) {
//     console.error(`Error on commit for June ${i}, 2024:`, error.message);
//     break;
//   }
// }

// console.log("✅ Auto commit process for June 2024 completed!");


const { execSync } = require("child_process");
const fs = require("fs");

const FILE_NAME = "auto_commit.txt";
const MIN_COMMITS_PER_DAY = 5;
const MAX_COMMITS_PER_DAY = 10;
const TOTAL_DAYS = 30; // June has 30 days

for (let day = 1; day <= TOTAL_DAYS; day++) {
  const commitCount = Math.floor(Math.random() * (MAX_COMMITS_PER_DAY - MIN_COMMITS_PER_DAY + 1)) + MIN_COMMITS_PER_DAY;

  console.log(`\n📅 Dec ${day}, 2024 - ${commitCount} commits`);

  for (let commitNum = 1; commitNum <= commitCount; commitNum++) {
    const commitDate = new Date(`2024-12-${String(day).padStart(2, "0")}T${String(commitNum + 10).padStart(2, "0")}:00:00`);

    // Modify file to ensure change
    fs.writeFileSync(FILE_NAME, `Commit on Dec ${day}, 2024 - #${commitNum} at ${commitDate.toISOString()}`);

    try {
      // Set commit date environment variables
      const gitEnv = `GIT_COMMITTER_DATE="${commitDate.toISOString()}" GIT_AUTHOR_DATE="${commitDate.toISOString()}"`;

      // Run Git commands
      execSync("git add .");
      execSync(`${gitEnv} git commit -m "Commit #${commitNum} on Dec ${day}, 2024"`, { stdio: "inherit" });

      console.log(`✅ Commit #${commitNum} for Dec ${day} created.`);
    } catch (error) {
      console.error(`❌ Error on commit #${commitNum} for Dec ${day}:`, error.message);
      break;
    }
  }
}

// Push all commits together to avoid excessive push requests
try {
  execSync("git push origin main"); // Change 'main' if your branch is different
  console.log("\n🚀 All commits pushed successfully!");
} catch (error) {
  console.error("❌ Error pushing commits:", error.message);
}
