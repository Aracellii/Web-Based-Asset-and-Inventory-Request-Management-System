<?xml version="1.0" encoding="UTF-8"?>
<configuration>
    <system.webServer>
        <defaultDocument>
            <files>
                <clear />
                <add value="index.php" />
            </files>
        </defaultDocument>
        
        <rewrite>
            <rules>
                <rule name="SIATK Redirect Trailing Slash" stopProcessing="true">
                    <match url="(.*[^/])$" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                    </conditions>
                    <action type="Redirect" url="{R:1}/" />
                </rule>

                <rule name="SIATK Clean URLs" stopProcessing="true">
                    <match url="^" ignoreCase="false" />
                    <conditions>
                        <add input="{REQUEST_FILENAME}" matchType="IsDirectory" negate="true" />
                        <add input="{REQUEST_FILENAME}" matchType="IsFile" negate="true" />
                    </conditions>
                    <action type="Rewrite" url="index.php" />
                </rule>
            </rules>
        </rewrite>

        <security>
            <requestFiltering>
                <requestLimits maxAllowedContentLength="104857600" /> 
            </requestFiltering>
        </security>

        <httpErrors errorMode="Detailed" />
    </system.webServer>
</configuration>
