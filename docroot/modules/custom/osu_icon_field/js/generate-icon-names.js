import fetch from "node-fetch"

const cliArguments = process.argv.slice();
const iconUrl = cliArguments.slice(2);

/**
 * Parse the CSS
 * @param osuIconUrl
 * @returns {Promise<RegExpMatchArray>}
 */
async function getOsuIconNames(osuIconUrl) {
    var iconNames = [];
    var response = await fetch(osuIconUrl, {
        headers: {
            'Access-Control-Allow-Origin': '*'
        },
    });
    var data = await response.text();

// parse css to get icon names
    iconNames = data.match(/\.icon-osu-[a-zA-Z0-9_-]*:/g);
// trim css class names down to just the icon names
    iconNames.forEach((name, i) => {
        iconNames[i] = name.replace('.icon-osu-', "").slice(0, -1);
    });

    return iconNames;
}

const osuIconList = await getOsuIconNames(iconUrl[0]);
osuIconList.sort();
osuIconList.forEach(iconName => {
    console.log(`'${iconName}',`);
});
