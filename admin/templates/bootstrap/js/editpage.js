
var jleHost = null;
var kcfinderPath, cKey, kKey, kSprache;

function editpageMain (jtlToken, templateUrl, _cKey, _kKey, _kSprache, _kcfinderPath)
{
    setJtlToken(jtlToken);

    cKey = _cKey;
    kKey = _kKey;
    kSprache = _kSprache;
    kcfinderPath = _kcfinderPath;

    window.kcfinderPath = kcfinderPath;
    window.jleHost      = new JLEHost(jtlToken, templateUrl, kcfinderPath, cKey, kKey, kSprache);
}