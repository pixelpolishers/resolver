exports.convertPlatform = function(platform) {
    var result;
    
    platform = platform || 'win32';
    
    switch (platform.toLowerCase()) {
        case 'win64':
            result = 'Win64';
            break;
            
        case 'win32':
        default:
            result = 'Win32';
            break;
    }
    
    return result;
};

exports.convertConfigurationType = function(configType) {
    var result;
    switch (configType) {
        case 'dynamic-library':
            result = 'DynamicLibrary';
            break;

        case 'static-library':
            result = 'StaticLibrary';
            break;
            
        case 'application':
        default:
            result = 'Application';
            break;
    }
    return result;
};

exports.convertWarningLevel = function(warningLevel) {
    var result;

    switch (warningLevel) {
        case 1:
            result = 'Level1';
            break;

        case 2:
            result = 'Level2';
            break;

        case 3:
            result = 'Level3';
            break;

        case 4:
            result = 'Level4';
            break;
            
        default:
            result = 'TurnOffAllWarnings';
            break;
    }
    return result;
};

exports.convertCharacterSet = function(characterSet) {
    var result;
    
    characterSet = characterSet || 'unicode';

    switch (characterSet) {
        case 'none':
            result = 'NotSet';
            break;
            
        case 'ansi':
            result = 'MultiByte';
            break;
            
        default:
        case 'unicode':
            result = 'Unicode';
            break;
    }
    
    return result;
};