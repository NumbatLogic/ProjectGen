<?php

	function ProjetGen_Xcode_Output($pSolution, $sAction)
	{
		$sBaseDirectory = ProjectGen_GetBaseDirectory($sAction);
			
		if (!is_dir($sBaseDirectory))
			mkdir($sBaseDirectory);
			
		for ($i = 0; $i < count($pSolution->m_pProjectArray); $i++)
		{
			$pProject = $pSolution->m_pProjectArray[$i];
			ProjetGen_Xcode_Project_Output($pSolution, $pProject, $sBaseDirectory);
		}
	}
	
	function ProjetGen_Xcode_Project_GetKey($pProject, $sName)
	{
		return substr(strtoupper(sha1($pProject->GetName() . $sName)), 0, 24) . " /* " . $sName . " *" . "/";
	}
	
	function ProjetGen_Xcode_Project_Output($pSolution, $pProject, $sBaseDirectory)
	{
		$sDependancyArray = ProjectGen_GetRecursiveDependancyArray($pSolution, $pProject);
		
		
		
		$xObjectArray = array();
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")] = array(
			"isa" => "PBXGroup",
			"children" => array(),
			"sourceTree" => "\"<group>\""
		);
		
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products")] = array(
			"isa" => "PBXGroup",
			"children" => array(
				//ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $pProject->GetName() . ".a")
			),
			"name" => "Products",
			"sourceTree" => "\"<group>\""
		);
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products");
		

		

		if ($pProject->GetKind() == KIND_CONSOLE_APP)
		{

			///A1B6A1B91AA1AA8400AA35EC /* JRPG.app */ = {isa = PBXFileReference; explicitFileType = wrapper.application; includeInIndex = 0; path = JRPG.app; sourceTree = BUILT_PRODUCTS_DIR; };
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $pProject->GetName() . ".app")] = array(
				"isa" => "PBXFileReference",
				"explicitFileType" => "wrapper.application",
				"includeInIndex" => 0,
				"path" => $pProject->GetName() . ".app",
				"sourceTree" => "BUILT_PRODUCTS_DIR"
			);
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products")] ["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $pProject->GetName() . ".app");
			
			
		
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName())] = array(
				"isa" => "PBXNativeTarget",
				"buildConfigurationList" => ProjetGen_Xcode_Project_GetKey($pProject, "XCConfigurationList PBXNativeTarget " . $pProject->GetName()),
				"buildPhases" => array(
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXSourcesBuildPhase Sources"),
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks"),
					
			//		ProjetGen_Xcode_Project_GetKey($pProject, "PBXCopyFilesBuildPhase CopyFiles"),
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXResourcesBuildPhase Resources"),
					
				),
				"buildRules" => array(),
				"dependencies" => array(),
				"name" => $pProject->GetName() ,
				"productName" => $pProject->GetName() ,
				"productReference" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $pProject->GetName() . ".app"),
				"productType" => "\"com.apple.product-type.application\"",
			);
		
			
			
			
		}
		else
		{
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference lib" . $pProject->GetName() . ".a")] = array(
				"isa" => "PBXFileReference",
				"explicitFileType" => "archive.ar",
				"includeInIndex" => 0,
				"path" => " lib" . $pProject->GetName() . ".a",
				"sourceTree" => "BUILT_PRODUCTS_DIR"
			);
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products")] ["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference lib" . $pProject->GetName() . ".a");
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName())] = array(
				"isa" => "PBXNativeTarget",
				"buildActionMask" => 2147483647,
				"files" => array (),
				"runOnlyForDeploymentPostprocessing" => 0,
			
				"buildConfigurationList" => ProjetGen_Xcode_Project_GetKey($pProject, "XCConfigurationList PBXNativeTarget " . $pProject->GetName()),
				"buildPhases" => array(
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXSourcesBuildPhase Sources"),
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks"),
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXCopyFilesBuildPhase CopyFiles"),
				),
				"buildRules" => array(),
				"dependencies" => array(),
				"name" => "\"" . $pProject->GetName() . "\"",		// maybe drop the quotes
				"productName" => "\"" . $pProject->GetName() . "\"",		// maybe drop the quotes
				"productReference" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference lib" . $pProject->GetName() . ".a"),
				"productType" => "\"com.apple.product-type.library.static\"",
			);
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXCopyFilesBuildPhase CopyFiles")] = array(
				"isa" => "PBXCopyFilesBuildPhase",
				"buildActionMask" => 2147483647,
				"dstPath" => "\"include/$(PRODUCT_NAME)\"",
				"dstSubfolderSpec" => 16,
				"files" => array (),
				"runOnlyForDeploymentPostprocessing" => 0
			);
		
		}
		
		
	
		$xObject = array(
			"isa" => "PBXFrameworksBuildPhase",
			"buildActionMask" => 2147483647,
			"files" => array (),
			"runOnlyForDeploymentPostprocessing" => 0
		);
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks")] = $xObject;
		
		
		
		$sConfigurationArray = array(
			CONFIGURATION_DEBUG,
			CONFIGURATION_RELEASE,
		);

		foreach ($sConfigurationArray as $sConfiguration)
		{
			$xObject = array(
				"isa" => "XCBuildConfiguration",
				"buildSettings" => array(
					"ENABLE_BITCODE" => "NO", // enable one day, disabled for admob
					"ALWAYS_SEARCH_USER_PATHS" => "NO",
					"CLANG_CXX_LANGUAGE_STANDARD" => "\"gnu++0x\"",
					"CLANG_CXX_LIBRARY" => "\"libc++\"",
					"CLANG_ENABLE_MODULES" => "YES",
					"CLANG_ENABLE_OBJC_ARC" => "YES",
					"CLANG_WARN_BOOL_CONVERSION" => "YES",
					"CLANG_WARN_CONSTANT_CONVERSION" => "YES",
					"CLANG_WARN_DIRECT_OBJC_ISA_USAGE" => "YES_ERROR",
					"CLANG_WARN_EMPTY_BODY" => "YES",
					"CLANG_WARN_ENUM_CONVERSION" => "YES",
					"CLANG_WARN_INT_CONVERSION" => "YES",
					"CLANG_WARN_OBJC_ROOT_CLASS" => "YES_ERROR",
					"CLANG_WARN_UNREACHABLE_CODE" => "YES",
					"CLANG_WARN__DUPLICATE_METHOD_MATCH" => "YES",
					"ENABLE_STRICT_OBJC_MSGSEND" => "YES",
					"GCC_C_LANGUAGE_STANDARD" => "gnu99",
					"GCC_WARN_64_TO_32_BIT_CONVERSION" => "YES",
					"GCC_WARN_ABOUT_RETURN_TYPE" => "YES_ERROR",
					"GCC_WARN_UNDECLARED_SELECTOR" => "YES",
					"GCC_WARN_UNINITIALIZED_AUTOS" => "YES_AGGRESSIVE",
					"GCC_WARN_UNUSED_FUNCTION" => "YES",
					"GCC_WARN_UNUSED_VARIABLE" => "YES",
					"HEADER_SEARCH_PATHS" => array(
						"\"$(inherited)\"",
						"/Applications/Xcode.app/Contents/Developer/Toolchains/XcodeDefault.xctoolchain/usr/include",
					),
					"IPHONEOS_DEPLOYMENT_TARGET" => "8.1",
					"ONLY_ACTIVE_ARCH" => "YES",
					"SDKROOT" => "iphoneos",
				),
				"name" => $sConfiguration,
			);
			
			if ($sConfiguration == CONFIGURATION_DEBUG)
			{
				$xObject["buildSettings"]["COPY_PHASE_STRIP"] = "NO";
				$xObject["buildSettings"]["ENABLE_TESTABILITY"] = "YES";
				$xObject["buildSettings"]["GCC_DYNAMIC_NO_PIC"] = "NO";
				$xObject["buildSettings"]["GCC_OPTIMIZATION_LEVEL"] = 0;
				$xObject["buildSettings"]["GCC_PREPROCESSOR_DEFINITIONS"] = array(
					"\"DEBUG=1\"",
					"\"NB_DEBUG=1\"",
					"\"$(inherited)\"",
				);
				$xObject["buildSettings"]["GCC_SYMBOLS_PRIVATE_EXTERN"] = "NO";
				$xObject["buildSettings"]["MTL_ENABLE_DEBUG_INFO"] = "YES";
			}
			else
			{
				$xObject["buildSettings"]["COPY_PHASE_STRIP"] = "YES";
				$xObject["buildSettings"]["ENABLE_NS_ASSERTIONS"] = "NO";
				$xObject["buildSettings"]["MTL_ENABLE_DEBUG_INFO"] = "NO";
				$xObject["buildSettings"]["VALIDATE_PRODUCT"] = "YES";
			}
			
			if ($pProject->GetKind() == KIND_CONSOLE_APP)
			{
				$xObject["buildSettings"]["\"CODE_SIGN_IDENTITY[sdk=iphoneos*]\""] = "\"iPhone Developer\"";
				$xObject["buildSettings"]["TARGETED_DEVICE_FAMILY"] = "\"1,2\"";
				
				//ENABLE_STRICT_OBJC_MSGSEND = YES;
			}
		
			$sIncludeDirectoryArray = $pProject->GetIncludeDirectoryArray($sConfiguration, ARCHITECTURE_64);
			foreach ($sIncludeDirectoryArray as $sHeaderPath)
				$xObject["buildSettings"]["HEADER_SEARCH_PATHS"][] = ProjectGen_GetRelativePath(realpath($sBaseDirectory), realpath($pProject->GetBaseDirectory() . "/" . $sHeaderPath));
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "XCBuildConfiguration PBXProject " . $pProject->GetName() . " " . $sConfiguration)] = $xObject;
			
			
			$xObject = array(
				"isa" => "XCBuildConfiguration",
				"buildSettings" => array(
					"OTHER_LDFLAGS" => "\"-ObjC\"",
					"PRODUCT_NAME" => "\"$(TARGET_NAME)\"",
					
					"LIBRARY_SEARCH_PATHS" => array(
						"\"$(inherited)\"",
					),

					"FRAMEWORK_SEARCH_PATHS" => array(
						"\"$(PROJECT_DIR)\"",
					),
				),
				"name" => $sConfiguration,
			);
			
			if ($pProject->GetKind() == KIND_CONSOLE_APP)
			{
//				$xObject["buildSettings"]["\"CODE_SIGN_IDENTITY[sdk=iphoneos*]\""] = "iPhone Developer";
			//	$xObject["buildSettings"]["TARGETED_DEVICE_FAMILY"] = "\"1,2\"";
				//ENABLE_STRICT_OBJC_MSGSEND = YES;
				
				
				$xObject["buildSettings"]["ASSETCATALOG_COMPILER_APPICON_NAME"] = "AppIcon";
				$xObject["buildSettings"]["ASSETCATALOG_COMPILER_LAUNCHIMAGE_NAME"] = "LaunchImage";
				$xObject["buildSettings"]["CODE_SIGN_IDENTITY"] = "\"iPhone Developer\"";
				$xObject["buildSettings"]["\"CODE_SIGN_IDENTITY[sdk=iphoneos*]\""] = "\"iPhone Developer\"";
				$xObject["buildSettings"]["COMPRESS_PNG_FILES"] = "NO";
				$xObject["buildSettings"]["STRIP_PNG_TEXT"] = "NO";
				/*HEADER_SEARCH_PATHS = (
					"$(inherited)",
					/Applications/Xcode.app/Contents/Developer/Toolchains/XcodeDefault.xctoolchain/usr/include,
					../../3rdParty,
					../../Engine,
					../../Core,
					../../../Library/iOs/libcurl/include,
				);*/
				$xObject["buildSettings"]["INFOPLIST_FILE"] = $pProject->GetName() . ".plist";
				$xObject["buildSettings"]["IPHONEOS_DEPLOYMENT_TARGET"] = "8.1"; // dupe?
				$xObject["buildSettings"]["LD_RUNPATH_SEARCH_PATHS"] = "\"$(inherited) @executable_path/Frameworks\"";
				/*LIBRARY_SEARCH_PATHS = (
					"$(inherited)",
					../../../Library/iOs/libcurl/lib,
				);*/
				$xObject["buildSettings"]["PRODUCT_BUNDLE_IDENTIFIER"] = $pProject->GetBundleIdentifier();
				$xObject["buildSettings"]["PRODUCT_NAME"] = "\"$(TARGET_NAME)\"";
				$xObject["buildSettings"]["PROVISIONING_PROFILE"] = "\"\"";
		
				
				// Plist hax
				$sTemp = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>" .
					"<!DOCTYPE plist PUBLIC \"-//Apple//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">" .
					"<plist version=\"1.0\">" .
					"<dict>" .
					"	<key>CFBundleDevelopmentRegion</key>" .
					"	<string>en</string>" .
					"	<key>CFBundleDisplayName</key>" .
					"	<string>" . $pProject->GetFriendlyName() . "</string>" .
					"	<key>CFBundleExecutable</key>" .
					"	<string>$(EXECUTABLE_NAME)</string>" .
					"	<key>CFBundleIdentifier</key>" .
					"	<string>$(PRODUCT_BUNDLE_IDENTIFIER)</string>" .
					"	<key>CFBundleInfoDictionaryVersion</key>" .
					"	<string>6.0</string>" .
					"	<key>CFBundleName</key>" .
					"	<string>$(PRODUCT_NAME)</string>" .
					"	<key>CFBundlePackageType</key>" .
					"	<string>APPL</string>" .
					"	<key>CFBundleShortVersionString</key>" .
					"	<string>1.0</string>" .
					"	<key>CFBundleSignature</key>" .
					"	<string>????</string>" .
					"	<key>CFBundleVersion</key>" .
					"	<string>1</string>" .
					"	<key>LSRequiresIPhoneOS</key>" .
					"	<true/>" .
					//"	<key>UILaunchStoryboardName</key>" .
					//"	<string>Launch Screen</string>" .
					"	<key>UIRequiredDeviceCapabilities</key>" .
					"	<array>" .
					"		<string>armv7</string>" .
					"	</array>" .
					"	<key>UIRequiresFullScreen</key>" .
					"	<true/>" .
					"	<key>UIStatusBarHidden</key>" .
					"	<true/>" .
					"	<key>UISupportedInterfaceOrientations</key>" .
					"	<array>" .
					"		<string>UIInterfaceOrientationPortrait</string>" .
					"		<string>UIInterfaceOrientationLandscapeLeft</string>" .
					"		<string>UIInterfaceOrientationLandscapeRight</string>" .
					"		<string>UIInterfaceOrientationPortrait</string>" .
					"		<string>UIInterfaceOrientationPortraitUpsideDown</string>" .
					"	</array>" .
					"	<key>UISupportedInterfaceOrientations~ipad</key>" .
					"	<array>" .
					"		<string>UIInterfaceOrientationPortrait</string>" .
					"		<string>UIInterfaceOrientationLandscapeLeft</string>" .
					"		<string>UIInterfaceOrientationLandscapeRight</string>" .
					"		<string>UIInterfaceOrientationPortrait</string>" .
					"		<string>UIInterfaceOrientationPortraitUpsideDown</string>" .
					"	</array>" .
					"	<key>UIViewControllerBasedStatusBarAppearance</key>" .
					"	<false/>" .
					"</dict>" .
					"</plist>";
				file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . ".plist", $sTemp);
			}
			else
			{
				$xObject["buildSettings"]["SKIP_INSTALL"] = "YES";
			}
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "XCBuildConfiguration PBXNativeTarget " . $pProject->GetName() . " " . $sConfiguration)] = $xObject;
		}
		
		$sThingArray = array("PBXProject", "PBXNativeTarget");
		foreach ($sThingArray as $sThing)
		{
			$xObject = array(
				"isa" => "XCConfigurationList",
				"buildConfigurations" => array(
				),
				"defaultConfigurationIsVisible" => 0,
				"defaultConfigurationName" => CONFIGURATION_RELEASE,
			);
			
			foreach ($sConfigurationArray as $sConfiguration)
				$xObject["buildConfigurations"][] = ProjetGen_Xcode_Project_GetKey($pProject, "XCBuildConfiguration " . $sThing . " " . $pProject->GetName() . " " . $sConfiguration);
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "XCConfigurationList " . $sThing . " " . $pProject->GetName())] = $xObject;
		}
		
		
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXSourcesBuildPhase Sources")] = array(
			"isa" => "PBXSourcesBuildPhase",
			"buildActionMask" => 2147483647,
			"files" => array (),
			"runOnlyForDeploymentPostprocessing" => 0
		);
		
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXResourcesBuildPhase Resources")] = array(
			"isa" => "PBXResourcesBuildPhase",
			"buildActionMask" => 2147483647,
			"files" => array (),
			"runOnlyForDeploymentPostprocessing" => 0
		);
		
		
		$xObject = array(
			"isa" => "PBXProject",
			"attributes" => array(
				"LastUpgradeCheck" => "0700",
				"ORGANIZATIONNAME" => "\"Numbat Logic\"",
				"TargetAttributes" => array(
					ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName()) => array(
						"CreatedOnToolsVersion" => "6.1.1",
					),
				),
			),
			"buildConfigurationList" => ProjetGen_Xcode_Project_GetKey($pProject, "XCConfigurationList PBXProject " . $pProject->GetName()),
			"compatibilityVersion" => "\"Xcode 3.2\"",
			"developmentRegion" => "English",
			"hasScannedForEncodings" => 0,
			"knownRegions" => array("en"),
			"mainGroup" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup"),
			"productRefGroup" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products"),
			"projectDirPath" => "\"\"",
			"projectRoot" => "\"\"",
			"targets" => array(
				ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName()),
			),
		);
		
		
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXProject Project object")] = $xObject;
		
		

		// frameworks folder
		$xFolderObject = array(
			"isa" => "PBXGroup",
			"children" => array(),
			"name" => "\"Frameworks\"",
			"sourceTree" => "\"<group>\""
		);
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Frameworks")] = $xFolderObject;
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Frameworks");


		
		
		// add dependancies
		if ($pProject->GetKind() == KIND_CONSOLE_APP)
		{
			
			//PBXProject Project object
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXProject Project object")]["attributes"]["TargetAttributes"][ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName())]["DevelopmentTeam"] = "XQ485NAN73";
			
			
			
			for ($i = 0; $i < count($sDependancyArray); $i++)
			{
				$sDependancy = $sDependancyArray[$i];
				$pDependancy = $pSolution->GetProjectByName($sDependancy);
				
				if ($pDependancy)
				{
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sDependancy . ".xcodeproj")] = array(
						"isa" => "PBXFileReference",
						"lastKnownFileType" => "\"wrapper.pb-project\"",
						"path" => $sDependancy . ".xcodeproj",
						"sourceTree" => "\"<group>\""
					);
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sDependancy . ".xcodeproj");
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXContainerItemProxy PBXReferenceProxy " . $sDependancy . ".xcodeproj")] = array(
						"isa" => "PBXContainerItemProxy",
						"containerPortal" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sDependancy . ".xcodeproj"),
						"remoteGlobalIDString" => ProjetGen_Xcode_Project_GetKey($pDependancy, "PBXFileReference lib" . $pDependancy->GetName() . ".a"),
						"remoteInfo" => $sDependancy,
						"proxyType" => "2",
					);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXReferenceProxy lib" . $sDependancy . ".a")] = array(
						"isa" => "PBXReferenceProxy",
						"fileType" => "archive.ar",
						"path" => "lib" . $sDependancy . ".a",
						"remoteRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXContainerItemProxy PBXReferenceProxy " . $sDependancy . ".xcodeproj"),
						"sourceTree" => "BUILT_PRODUCTS_DIR"
					);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile lib" . $sDependancy . ".a in Frameworks")] = array(
						"isa" => "PBXBuildFile",
						"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXReferenceProxy lib" . $sDependancy . ".a")
					);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile lib" . $sDependancy . ".a in Frameworks");
					
					// weird extra group
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products lib" . $sDependancy . ".a")] = array(
						"isa" => "PBXGroup",
						"children" => array(
							ProjetGen_Xcode_Project_GetKey($pProject, "PBXReferenceProxy lib" . $sDependancy . ".a")
						),
						"name" => "Products",
						"sourceTree" => "\"<group>\""
					);
					
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXProject Project object")]["projectReferences"][] = array(
						"ProductGroup" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products lib" . $sDependancy . ".a"),
						"ProjectRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sDependancy . ".xcodeproj"),
					);
					
					
					
				
					
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXContainerItemProxy PBXTargetDependency " . $sDependancy . ".xcodeproj")] = array(
						"isa" => "PBXContainerItemProxy",
						"containerPortal" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sDependancy . ".xcodeproj"),
						"remoteGlobalIDString" => ProjetGen_Xcode_Project_GetKey($pDependancy, "PBXNativeTarget " . $pDependancy->GetName()),
						"remoteInfo" => $sDependancy,
						"proxyType" => "1",
					);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXTargetDependency " . $sDependancy . ".xcodeproj")] = array(
						"isa" => "PBXTargetDependency",
						"name" => $sDependancy,
						"targetProxy" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXContainerItemProxy PBXTargetDependency " . $sDependancy . ".xcodeproj"),
					);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName())]["dependencies"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXTargetDependency " . $sDependancy . ".xcodeproj");
				}
				else
				{
					//echo $sDependancy . "\n";
					$sRelativePath = $sDependancy;
					if ($sRelativePath[0] != "/")
						$sRelativePath = ProjectGen_GetRelativePath(realpath($sBaseDirectory), realpath($pProject->GetBaseDirectory() . "/" . $sDependancy));
					$sDirName = pathinfo($sRelativePath, PATHINFO_DIRNAME);
					$sBaseName = pathinfo($sDependancy, PATHINFO_BASENAME);
					$sExtension = pathinfo($sDependancy, PATHINFO_EXTENSION);
					
					if ($sExtension == "framework")
					{
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)] = array(
							"isa" => "PBXFileReference",
							"lastKnownFileType" => "wrapper.framework",
							"name" => $sBaseName,
							"path" => $sRelativePath,
							"sourceTree" => "\"<group>\"" //"SDKROOT"
						);

						foreach ($sConfigurationArray as $sConfiguration)
						{
							$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "XCBuildConfiguration PBXNativeTarget " . $pProject->GetName() . " " . $sConfiguration)]["buildSettings"]["FRAMEWORK_SEARCH_PATHS"][] = $sDirName;
						}
					}
					else if ($sExtension == "tbd")
					{
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)] = array(
							"isa" => "PBXFileReference",
							"lastKnownFileType" => "\"sourcecode.text-based-dylib-definition\"",
							"name" => "\"" . $sBaseName . "\"",
							"path" => "\"" . $sDependancy . "\"",
							"sourceTree" => "SDKROOT"
						);
					
				//	A1A54BC51C58D379002CE916 /* libz.tbd */ = {isa = PBXFileReference; lastKnownFileType = "sourcecode.text-based-dylib-definition"; name = libz.tbd; path = usr/lib/libz.tbd; sourceTree = SDKROOT; };
					}
					else
					{
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)] = array(
							"isa" => "PBXFileReference",
							"lastKnownFileType" => "archive.ar",
							"name" => $sBaseName,
							"path" => $sRelativePath,
							"sourceTree" => "\"<group>\""
						);
						
						foreach ($sConfigurationArray as $sConfiguration)
						{
							$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "XCBuildConfiguration PBXNativeTarget " . $pProject->GetName() . " " . $sConfiguration)]["buildSettings"]["LIBRARY_SEARCH_PATHS"][] = $sDirName;
						}
					}
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sBaseName . " in Frameworks")] = array(
						"isa" => "PBXBuildFile",
						"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)
					);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Frameworks")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sBaseName . " in Frameworks");
				}
			}
		}
		else
		{
			// keep frameworks
			
			for ($i = 0; $i < count($sDependancyArray); $i++)
			{
				$sDependancy = $sDependancyArray[$i];
				$pDependancy = $pSolution->GetProjectByName($sDependancy);
				
				if (!$pDependancy)
				{
					$sRelativePath = $sDependancy;
					if ($sRelativePath[0] != "/")
						$sRelativePath = ProjectGen_GetRelativePath(realpath($sBaseDirectory), realpath($pProject->GetBaseDirectory() . "/" . $sDependancy));
					$sDirName = pathinfo($sRelativePath, PATHINFO_DIRNAME);
					$sBaseName = pathinfo($sDependancy, PATHINFO_BASENAME);
					$sExtension = pathinfo($sDependancy, PATHINFO_EXTENSION);
					
					if ($sExtension == "framework")
					{
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)] = array(
							"isa" => "PBXFileReference",
							"lastKnownFileType" => "wrapper.framework",
							"name" => $sBaseName,
							"path" => $sRelativePath,
							"sourceTree" => "\"<group>\"" //"SDKROOT"
						);

						foreach ($sConfigurationArray as $sConfiguration)
						{
							$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "XCBuildConfiguration PBXNativeTarget " . $pProject->GetName() . " " . $sConfiguration)]["buildSettings"]["FRAMEWORK_SEARCH_PATHS"][] = $sDirName;
						}
					
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sBaseName . " in Frameworks")] = array(
							"isa" => "PBXBuildFile",
							"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)
						);
						
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Frameworks")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName);
						
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sBaseName . " in Frameworks");
					}
				}
			}
		}
		
		
		// Icons
		if ($pProject->GetKind() == KIND_CONSOLE_APP)
		{
			$sDirectory = $sBaseDirectory . "/" . $pProject->GetName();
			if (!is_dir($sDirectory))
				mkdir($sDirectory);

			$sDirectory .= "/Images.xcassets";
			if (!is_dir($sDirectory))
				mkdir($sDirectory);

			$xBuildFileObject = array(
				"isa" => "PBXBuildFile",
				"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference Images.xcassets")
			);
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile Images.xcassets in Resources")] = $xBuildFileObject;

			//A19CE1A81E92C987002A16A8 /* Images-2.xcassets */ = {isa = PBXFileReference; lastKnownFileType = folder.assetcatalog; name = "Images-2.xcassets"; path = "Client/Images-2.xcassets"; sourceTree = "<group>"; };
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference Images.xcassets")] = array(
				"isa" => "PBXFileReference",
				"lastKnownFileType" => "folder.assetcatalog",
				"name" => "\"Images.xcassets\"",
				"path" => "\"" . $pProject->GetName() . "/Images.xcassets\"",
				"sourceTree" => "\"<group>\"",
			);

			//$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Products")] ["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference Images.xcassets");
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference Images.xcassets");
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXResourcesBuildPhase Resources")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile Images.xcassets in Resources");
			//$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup " . $sFolderName)] = $xFolderObject;*/



			// Icons
				$sDirectory .= "/AppIcon.appiconset";
				if (!is_dir($sDirectory))
					mkdir($sDirectory);

				$xImageArray = array(
					20.0, 2.0, "iphone",
					20.0, 3.0, "iphone",
					29.0, 2.0, "iphone",
					29.0, 3.0, "iphone",
					40.0, 2.0, "iphone",
					40.0, 3.0, "iphone",
					60.0, 2.0, "iphone",
					60.0, 3.0, "iphone",

					20.0, 1.0, "ipad",
					20.0, 2.0, "ipad",
					29.0, 1.0, "ipad",
					29.0, 2.0, "ipad",
					40.0, 1.0, "ipad",
					40.0, 2.0, "ipad",
					76.0, 1.0, "ipad",
					76.0, 2.0, "ipad",
					83.5, 2.0, "ipad",

					1024.0, 1.0, "ios-marketing");

				$sJson = "";
				$sJson .= "{\n";
				$sJson .= "  \"images\" : [\n";

				for ($i = 0; $i < count($xImageArray); $i += 3)
				{
					//$sFileName = $xImageArray[$i+2] . "_" . $xImageArray[$i+0] . "x" . $xImageArray[$i+0] . "x" . $xImageArray[$i+1] . ".png";
					$sFileName = "Icon-" . $xImageArray[$i+0] . "@" . $xImageArray[$i+1] . "x.png";
					$nSize = intval($xImageArray[$i+0] * $xImageArray[$i+1]);
					if (!ProjectGen_SvgRender($pProject->GetIcon(), "", $sDirectory . "/" . $sFileName, $nSize))
						echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sDirectory . "/" . $sFileName . "\n";

					$sJson .= "    {\n";
					$sJson .= "      \"size\" : \"" . $xImageArray[$i+0] . "x" . $xImageArray[$i+0] . "\",\n";
					$sJson .= "      \"idiom\" : \"" . $xImageArray[$i+2] . "\",\n";
					$sJson .= "      \"filename\" : \"" . $sFileName . "\",\n";
					$sJson .= "      \"scale\" : \"" . $xImageArray[$i+1] . "x\"\n";
					$sJson .= "    }" . ($i < count($xImageArray)-3 ? "," : "") . "\n";
				}

				$sJson .= "  ],\n";
				$sJson .= "  \"info\" : {\n";
				$sJson .= "    \"version\" : 1,\n";
				$sJson .= "    \"author\" : \"xcode\"\n";
				$sJson .= "  }\n";
				$sJson .= "}\n";

				file_put_contents($sDirectory . "/Contents.json" , $sJson);

			// Launch Images
				$sDirectory = $sBaseDirectory . "/" . $pProject->GetName() . "/Images.xcassets/LaunchImage.launchimage";
				if (!is_dir($sDirectory))
					mkdir($sDirectory);

				$nNumElement = 8;
				$xImageArray = array(
					"portrait",		"iphone",	"full-screen",		"8.0",	"736h",		"3x",	1242,	2208,	//"Default-736h@3x.png",
					"landscape",	"iphone",	"full-screen",		"8.0",	"736h",		"3x",	2208,	1242,	//"Default-Landscape-736h@3x.png",
					"portrait",		"iphone",	"full-screen",		"8.0",	"667h",		"2x",	750,	1334,	//"Default-667h@2x.png",
					"portrait",		"iphone",	"full-screen",		"7.0",	NULL,		"2x",	640,	960,
					"portrait",		"iphone",	"full-screen",		"7.0",	"retina4",	"2x",	640,	1136,
					"portrait",		"ipad",		"full-screen",		"7.0",	NULL,		"1x",	768,	1024,
					"landscape",	"ipad",		"full-screen",		"7.0",	NULL,		"1x",	1024,	768,
					"portrait",		"ipad",		"full-screen",		"7.0",	NULL,		"2x",	1536,	2048,
					"landscape",	"ipad",		"full-screen",		"7.0",	NULL,		"2x",	2048,	1536,
					"portrait",		"iphone",	"full-screen",		NULL,	NULL,		"1x",	320,	480,
					"portrait",		"iphone",	"full-screen",		NULL,	NULL,		"2x",	640,	960,
					"portrait",		"iphone",	"full-screen",		NULL,	"retina4",	"2x",	640,	1136,
					"portrait",		"ipad",		"to-status-bar",	NULL,	NULL,		"1x",	768,	1004,
					"portrait",		"ipad",		"full-screen",		NULL,	NULL,		"1x",	768,	1024,
					"landscape",	"ipad",		"to-status-bar",	NULL,	NULL,		"1x",	1024,	748,
					"landscape",	"ipad",		"full-screen",		NULL,	NULL,		"1x",	1024,	768,
					"portrait",		"ipad",		"to-status-bar",	NULL,	NULL,		"2x",	1536,	2008,
					"portrait",		"ipad",		"full-screen",		NULL,	NULL,		"2x",	1536,	2048,
					"landscape",	"ipad",		"to-status-bar",	NULL,	NULL,		"2x",	2048,	1496,
					"landscape",	"ipad",		"full-screen",		NULL,	NULL,		"2x",	2048,	1536,
				);

				$sJson = "";
				$sJson .= "{\n";
				$sJson .= "  \"images\" : [\n";

				for ($i = 0; $i < count($xImageArray); $i += $nNumElement)
				{
				/*	//$sFileName = $xImageArray[$i+2] . "_" . $xImageArray[$i+0] . "x" . $xImageArray[$i+0] . "x" . $xImageArray[$i+1] . ".png";
					$sFileName = "Icon-" . $xImageArray[$i+0] . "@" . $xImageArray[$i+1] . "x.png";
					$nSize = intval($xImageArray[$i+0] * $xImageArray[$i+1]);
					if (!ProjectGen_SvgRender($pProject->GetIcon(), "", $sDirectory . "/" . $sFileName, $nSize))
						echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sDirectory . "/" . $sFileName . "\n";
*/

					$sFileName = $xImageArray[$i+0] .
								$xImageArray[$i+1] .
								$xImageArray[$i+2] .
								$xImageArray[$i+3] .
								$xImageArray[$i+4] .
								$xImageArray[$i+5] .
								$xImageArray[$i+6] .
								$xImageArray[$i+7] .
								".png";

					$pImage = imagecreatetruecolor($xImageArray[$i+6], $xImageArray[$i+7]); 
					$pColour = imagecolorallocate($pImage, 255, 255, 255);
					imagefill($pImage , 0,0 , $pColour);
					imagepng($pImage, $sDirectory . "/" . $sFileName);

					$sJson .= "    {\n";
					$sJson .= "      \"orientation\" : \"" . $xImageArray[$i+0] . "\",\n";
					$sJson .= "      \"idiom\" : \"" . $xImageArray[$i+1] . "\",\n";
					$sJson .= "      \"extent\" : \"" . $xImageArray[$i+2] . "\",\n";
					if ($xImageArray[$i+3])
						$sJson .= "      \"minimum-system-version\" : \"" . $xImageArray[$i+3] . "\",\n";
					if ($xImageArray[$i+4])
						$sJson .= "      \"subtype\" : \"" . $xImageArray[$i+4] . "\",\n";
					$sJson .= "      \"scale\" : \"" . $xImageArray[$i+5] . "\",\n";
					$sJson .= "      \"filename\" : \"" . $sFileName . "\",\n";
					$sJson .= "    }" . ($i < count($xImageArray)-$nNumElement ? "," : "") . "\n";
				}

				$sJson .= "  ],\n";
				$sJson .= "  \"info\" : {\n";
				$sJson .= "    \"version\" : 1,\n";
				$sJson .= "    \"author\" : \"xcode\"\n";
				$sJson .= "  }\n";
				$sJson .= "}\n";

				file_put_contents($sDirectory . "/Contents.json" , $sJson);
		}
		
	
	
		// Add Sources
		ProjetGen_Xcode_Project_AddFileArray($pProject, $xObjectArray, TRUE, "Source", $sBaseDirectory, $pProject->m_xFileArray);
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Source");
		
		ProjetGen_Xcode_Project_AddFileArray($pProject, $xObjectArray, FALSE, "Resource", $sBaseDirectory, $pProject->m_xAssetArray);
		$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup Resource");
		
		$sOutput = ProjetGen_Xcode_Project_ToString($pProject, $xObjectArray);
		if (!is_dir($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj"))
			mkdir($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj");
		file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/" . "project.pbxproj", $sOutput);

		if (!is_dir($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/xcshareddata"))
			mkdir($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/xcshareddata");

		if (!is_dir($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/xcshareddata/xcschemes"))
			mkdir($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/xcshareddata/xcschemes");

		// scheme tech
		$sIdentifier = substr(ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName()), 0, 24);

		$sOutput = "";
		$sOutput .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$sOutput .= "<!DOCTYPE plist PUBLIC \"-//Apple//DTD PLIST 1.0//EN\" \"http://www.apple.com/DTDs/PropertyList-1.0.dtd\">\n";
		$sOutput .= "<plist version=\"1.0\">\n";
		$sOutput .= "<dict>\n";
		$sOutput .= "\t<key>SchemeUserState</key>\n";
		$sOutput .= "\t<dict>\n";
		$sOutput .= "\t\t<key>Client.xcscheme</key>\n";
		$sOutput .= "\t\t<dict>\n";
		$sOutput .= "\t\t\t<key>orderHint</key>\n";
		$sOutput .= "\t\t\t<integer>0</integer>\n";
		$sOutput .= "\t\t</dict>\n";
		$sOutput .= "\t</dict>\n";
		$sOutput .= "\t<key>SuppressBuildableAutocreation</key>\n";
		$sOutput .= "\t<dict>\n";
		$sOutput .= "\t\t<key>" . $sIdentifier . "</key>\n";
		$sOutput .= "\t\t<dict>\n";
		$sOutput .= "\t\t\t<key>primary</key>\n";
		$sOutput .= "\t\t\t<true/>\n";
		$sOutput .= "\t\t</dict>\n";
		$sOutput .= "\t</dict>\n";
		$sOutput .= "</dict>\n";
		$sOutput .= "</plist>";
		file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/xcshareddata/xcschemes/xcschememanagement.plist", $sOutput);
//			" . $pProject->GetName() . ".xcscheme", $sOutput);

		$sOutput = "";
		$sOutput .= "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n";
		$sOutput .= "<Scheme\n";
		$sOutput .= "   LastUpgradeVersion = \"0830\"\n";
		$sOutput .= "   version = \"1.3\">\n";
		$sOutput .= "   <BuildAction\n";
		$sOutput .= "      parallelizeBuildables = \"YES\"\n";
		$sOutput .= "      buildImplicitDependencies = \"YES\">\n";
		$sOutput .= "      <BuildActionEntries>\n";
		$sOutput .= "         <BuildActionEntry\n";
		$sOutput .= "            buildForTesting = \"YES\"\n";
		$sOutput .= "            buildForRunning = \"YES\"\n";
		$sOutput .= "            buildForProfiling = \"YES\"\n";
		$sOutput .= "            buildForArchiving = \"YES\"\n";
		$sOutput .= "            buildForAnalyzing = \"YES\">\n";
		$sOutput .= "            <BuildableReference\n";
		$sOutput .= "               BuildableIdentifier = \"primary\"\n";
		$sOutput .= "               BlueprintIdentifier = \"" . $sIdentifier . "\"\n";
		$sOutput .= "               BuildableName = \"" . $pProject->GetName() . ".app\"\n";
		$sOutput .= "               BlueprintName = \"" . $pProject->GetName() . "\"\n";
		$sOutput .= "               ReferencedContainer = \"container:" . $pProject->GetName() . ".xcodeproj\">\n";
		$sOutput .= "            </BuildableReference>\n";
		$sOutput .= "         </BuildActionEntry>\n";
		$sOutput .= "      </BuildActionEntries>\n";
		$sOutput .= "   </BuildAction>\n";
		$sOutput .= "   <TestAction\n";
		$sOutput .= "      buildConfiguration = \"release\"\n";
		$sOutput .= "      selectedDebuggerIdentifier = \"Xcode.DebuggerFoundation.Debugger.LLDB\"\n";
		$sOutput .= "      selectedLauncherIdentifier = \"Xcode.DebuggerFoundation.Launcher.LLDB\"\n";
		$sOutput .= "      shouldUseLaunchSchemeArgsEnv = \"YES\">\n";
		$sOutput .= "      <Testables>\n";
		$sOutput .= "      </Testables>\n";
		$sOutput .= "      <MacroExpansion>\n";
		$sOutput .= "         <BuildableReference\n";
		$sOutput .= "            BuildableIdentifier = \"primary\"\n";
		$sOutput .= "            BlueprintIdentifier = \"" . $sIdentifier . "\"\n";
		$sOutput .= "            BuildableName = \"" . $pProject->GetName() . ".app\"\n";
		$sOutput .= "            BlueprintName = \"" . $pProject->GetName() . "\"\n";
		$sOutput .= "            ReferencedContainer = \"container:" . $pProject->GetName() . ".xcodeproj\">\n";
		$sOutput .= "         </BuildableReference>\n";
		$sOutput .= "      </MacroExpansion>\n";
		$sOutput .= "      <AdditionalOptions>\n";
		$sOutput .= "      </AdditionalOptions>\n";
		$sOutput .= "   </TestAction>\n";
		$sOutput .= "   <LaunchAction\n";
		$sOutput .= "      buildConfiguration = \"release\"\n";
		$sOutput .= "      selectedDebuggerIdentifier = \"Xcode.DebuggerFoundation.Debugger.LLDB\"\n";
		$sOutput .= "      selectedLauncherIdentifier = \"Xcode.DebuggerFoundation.Launcher.LLDB\"\n";
		$sOutput .= "      launchStyle = \"0\"\n";
		$sOutput .= "      useCustomWorkingDirectory = \"NO\"\n";
		$sOutput .= "      ignoresPersistentStateOnLaunch = \"NO\"\n";
		$sOutput .= "      debugDocumentVersioning = \"YES\"\n";
		$sOutput .= "      debugServiceExtension = \"internal\"\n";
		$sOutput .= "      allowLocationSimulation = \"YES\">\n";
		$sOutput .= "     <BuildableProductRunnable\n";
		$sOutput .= "         runnableDebuggingMode = \"0\">\n";
		$sOutput .= "         <BuildableReference\n";
		$sOutput .= "            BuildableIdentifier = \"primary\"\n";
		$sOutput .= "            BlueprintIdentifier = \"" . $sIdentifier . "\"\n";
		$sOutput .= "            BuildableName = \"" . $pProject->GetName() . ".app\"\n";
		$sOutput .= "            BlueprintName = \"" . $pProject->GetName() . "\"\n";
		$sOutput .= "            ReferencedContainer = \"container:" . $pProject->GetName() . ".xcodeproj\">\n";
		$sOutput .= "         </BuildableReference>\n";
		$sOutput .= "      </BuildableProductRunnable>\n";
		$sOutput .= "      <AdditionalOptions>\n";
		$sOutput .= "      </AdditionalOptions>\n";
		$sOutput .= "   </LaunchAction>\n";
		$sOutput .= "   <ProfileAction\n";
		$sOutput .= "      buildConfiguration = \"release\"\n";
		$sOutput .= "      shouldUseLaunchSchemeArgsEnv = \"YES\"\n";
		$sOutput .= "      savedToolIdentifier = \"\"\n";
		$sOutput .= "      useCustomWorkingDirectory = \"NO\"\n";
		$sOutput .= "      debugDocumentVersioning = \"YES\">\n";
		$sOutput .= "      <BuildableProductRunnable\n";
		$sOutput .= "         runnableDebuggingMode = \"0\">\n";
		$sOutput .= "         <BuildableReference\n";
		$sOutput .= "            BuildableIdentifier = \"primary\"\n";
		$sOutput .= "            BlueprintIdentifier = \"" . $sIdentifier . "\"\n";
		$sOutput .= "            BuildableName = \"" . $pProject->GetName() . ".app\"\n";
		$sOutput .= "            BlueprintName = \"" . $pProject->GetName() . "\"\n";
		$sOutput .= "            ReferencedContainer = \"container:" . $pProject->GetName() . ".xcodeproj\">\n";
		$sOutput .= "         </BuildableReference>\n";
		$sOutput .= "      </BuildableProductRunnable>\n";
		$sOutput .= "   </ProfileAction>\n";
		$sOutput .= "   <AnalyzeAction\n";
		$sOutput .= "      buildConfiguration = \"release\">\n";
		$sOutput .= "   </AnalyzeAction>\n";
		$sOutput .= "   <ArchiveAction\n";
		$sOutput .= "      buildConfiguration = \"release\"\n";
		$sOutput .= "      revealArchiveInOrganizer = \"YES\">\n";
		$sOutput .= "   </ArchiveAction>\n";
		$sOutput .= "</Scheme>";
		file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . ".xcodeproj/xcshareddata/xcschemes/" . $pProject->GetName() . ".xcscheme", $sOutput);











		// Big icon for the store
		if ($pProject->GetKind() == KIND_CONSOLE_APP)
		{
			if (!ProjectGen_SvgRender($pProject->GetIcon(), "", $sBaseDirectory . "/StoreIcon.png", 1024))
				echo "*** Icon bad:\n\t" . $pProject->GetIcon() . "~" . $pProject->GetIconMask() . "\n\t" . $sBaseDirectory . "/StoreIcon.png" . "\n";
		}
	}
	
	function ProjetGen_Xcode_Project_AddFileArray($pProject, &$xObjectArray, $bSource, $sFolderName, $sBaseDirectory, $xFileArray)
		{
			$xFolderObject = array(
				"isa" => "PBXGroup",
				"children" => array(),
				"name" => "\"" . $sFolderName . "\"",
				"sourceTree" => "\"<group>\""
			);
			
			
			foreach ($xFileArray as $xFile)
			{
				switch ($xFile["sType"])
				{
					case FILE_TYPE_DIRECTORY:
					{
						ProjetGen_Xcode_Project_AddFileArray($pProject, $xObjectArray, $bSource, $xFile["sName"], $sBaseDirectory, $xFile["xFileArray"]);
						$xFolderObject["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup " . $xFile["sName"]);
						break;
					}
					case FILE_TYPE_FILE:
					{
						$xFileObject = array(
							"isa" => "PBXFileReference",
							"path" => ProjectGen_GetRelativePath(realpath($sBaseDirectory), $xFile["sPath"]),
							"name" => $xFile["sName"],
							"sourceTree" => "\"<group>\""
						);
						
						$sFileType = "file";
						switch ($xFile["sExtension"])
						{
							case "c":
								$xFileObject["lastKnownFileType"] = "sourcecode.c.c";
								$xFileObject["fileEncoding"] = 4;
								break;
							case "cpp":
								$xFileObject["lastKnownFileType"] = "sourcecode.cpp.cpp";
								$xFileObject["fileEncoding"] = 4;
								break;
							case "h":
								$xFileObject["lastKnownFileType"] = "sourcecode.c.h";
								$xFileObject["fileEncoding"] = 4;
								break;
							case "m":
								$xFileObject["lastKnownFileType"] = "sourcecode.c.objc";
								$xFileObject["fileEncoding"] = 4;
								break;
							case "plist":
								$xFileObject["lastKnownFileType"] = "text.plist.xml";
								$xFileObject["fileEncoding"] = 4;
								break;
							default:
								$xFileObject["lastKnownFileType"] = "file";
						}
						
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sFolderName . "/" . $xFile["sName"])] = $xFileObject;
						$xFolderObject["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sFolderName . "/" . $xFile["sName"]);
						
						if ($bSource)
						{
							if ($xFile["sExtension"] == "c" || $xFile["sExtension"] == "cpp" || $xFile["sExtension"] == "m")
							{
								//	A110847D1B32274E0064E12E /* Sync_InstanceFinish.c in Sources */ = {isa = PBXBuildFile; fileRef = A11084761B32274E0064E12E /* Sync_InstanceFinish.c */; };
								$xBuildFileObject = array(
									"isa" => "PBXBuildFile",
									"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sFolderName . "/" . $xFile["sName"])
								);
								$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sFolderName . "/" . $xFile["sName"] . " in Sources")] = $xBuildFileObject;
								
								$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXSourcesBuildPhase Sources")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sFolderName . "/" . $xFile["sName"] . " in Sources");
							}
						}
						else
						{
							$xBuildFileObject = array(
								"isa" => "PBXBuildFile",
								"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sFolderName . "/" . $xFile["sName"])
							);
							$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $xFile["sName"] . " in Resources")] = $xBuildFileObject;
							
							$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXResourcesBuildPhase Resources")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $xFile["sName"] . " in Resources");
						}
						
						break;
					}
					default:
					{
						throw new Exception("Oh hai! It's borked!");
					}
				}
			}
			
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup " . $sFolderName)] = $xFolderObject;
		}

	
	function ProjetGen_Xcode_Project_ToString($pProject, $xObjectArray)
	{
		
			$sIsaArray = Array("PBXBuildFile", "PBXCopyFilesBuildPhase", "PBXContainerItemProxy", "PBXFileReference", "PBXFrameworksBuildPhase", "PBXGroup", "PBXNativeTarget", "PBXProject", "PBXReferenceProxy", "PBXResourcesBuildPhase", "PBXSourcesBuildPhase", "PBXTargetDependency", "XCBuildConfiguration", "XCConfigurationList");
		
			$sOutput = "";
			$sOutput .= "// !$*UTF8*$!\n";
			$sOutput .= "{\n";
				$sOutput .= "\tarchiveVersion = 1;\n";
				$sOutput .= "\tclasses = {\n";
				$sOutput .= "\t};\n";
				$sOutput .= "\tobjectVersion = 46;\n";
				$sOutput .= "\tobjects = {\n";
				
				foreach ($sIsaArray as $sIsa)
				{
					$sOutput .= "\n";
					$sOutput .= "/* Begin " . $sIsa . " section */\n";
					
					foreach ($xObjectArray as $sKey => $xObject)
					{
						if (isset($xObject["isa"]) && $xObject["isa"] == $sIsa)
						{
							$sOutput .= "\t" . $sKey . " = " . ProjetGen_Xcode_Project_Object_ToString($xObject, 1) . ";\n";
						}
					}
					
					$sOutput .= "/* End " . $sIsa . " section */\n";
				}
				
				$sOutput .= "\t};\n";
				$sOutput .= "\trootObject = " . ProjetGen_Xcode_Project_GetKey($pProject, "PBXProject Project object") . ";\n";
			$sOutput .= "}";
			
			return $sOutput;
			
		}
		
		
		
		
		function ProjetGen_Xcode_Project_Object_ToString($xObject, $nTabDepth)
		{
			$sTabs = "";
			for ($i = 0; $i < $nTabDepth; $i++)
				$sTabs .= "\t";
			
			$sOutput = "";
			
			if (count($xObject) > 0 && array_keys($xObject) !== range(0, count($xObject) - 1)) // associative array
			{
				$bInline = FALSE;
				if (isset($xObject["isa"]) && ($xObject["isa"] == "PBXBuildFile" || $xObject["isa"] == "PBXFileReference"))
					$bInline = TRUE;
				
				$sOutput .= "{" . ($bInline ? "" : "\n");
					foreach ($xObject as $sName => $xSubObject)
					{
						$sOutput .= ($bInline ? " " : $sTabs . "\t") . $sName . " = ";

						if (is_array($xSubObject))
							$sOutput .= ProjetGen_Xcode_Project_Object_ToString($xSubObject, $nTabDepth+1);
						else
							$sOutput .= $xSubObject;
						
						$sOutput .= ";" . ($bInline ? "" : "\n");
					}
				$sOutput .= ($bInline ? " " : $sTabs) . "}";
			}
			else
			{
				$sOutput .= "(\n";
				foreach ($xObject as $xSubObject)
				{
					if (is_string($xSubObject) || is_int($xSubObject))
						$sOutput .=  $sTabs . "\t" . $xSubObject . ",\n";
					else
						$sOutput .= $sTabs . "\t" . ProjetGen_Xcode_Project_Object_ToString($xSubObject, $nTabDepth+1) . ",\n";
				}
				$sOutput .= $sTabs . ")";
			}
			
			return $sOutput;
			
		}
	
?>
