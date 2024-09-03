import fetch from "node-fetch"

const cliArguments = process.argv.slice();
const iconUrl = cliArguments.slice(2);

/**
 * Parse the CSS
 * @param faIconUrl
 * @returns {Promise<RegExpMatchArray>}
 */
async function getOsuIconNames(faIconUrl) {
    var iconNames = [];
    var response = await fetch(faIconUrl, {
        headers: {
            'Access-Control-Allow-Origin': '*'
        },
    });
    var data = await response.text();

// parse css to get icon names
    iconNames = data.match(/\.fa-[a-zA-Z0-9_-]*:/g);
// trim css class names down to just the icon names
    iconNames.forEach((name, i) => {
        iconNames[i] = name.replace('.fa-', "").slice(0, -1);
    });

    return iconNames;
}

const osuIconList = await getOsuIconNames(iconUrl[0]);
osuIconList.sort();
osuIconList.forEach(iconName => {
    console.log(`'${iconName}',`);
});
