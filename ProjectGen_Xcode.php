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
				),
				"name" => $sConfiguration,
			);
			
			if ($pProject->GetKind() == KIND_CONSOLE_APP)
			{
//				$xObject["buildSettings"]["\"CODE_SIGN_IDENTITY[sdk=iphoneos*]\""] = "iPhone Developer";
			//	$xObject["buildSettings"]["TARGETED_DEVICE_FAMILY"] = "\"1,2\"";
				//ENABLE_STRICT_OBJC_MSGSEND = YES;
				
				
				$xObject["buildSettings"]["ASSETCATALOG_COMPILER_APPICON_NAME"] = "AppIcon";
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
					"	<key>UILaunchStoryboardName</key>" .
					"	<string>Launch Screen</string>" .
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
					"	</array>" .
					"	<key>UISupportedInterfaceOrientations~ipad</key>" .
					"	<array>" .
					"		<string>UIInterfaceOrientationPortrait</string>" .
					"	</array>" .
					"	<key>UIViewControllerBasedStatusBarAppearance</key>" .
					"	<false/>" .
					"</dict>" .
					"</plist>";
				file_put_contents($sBaseDirectory . "/" . $pProject->GetName() . ".plist", $sTemp);
			}
			else
			{
				$xObject["SKIP_INSTALL"] = "YES";
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
		
		
		
		
		// add dependancies
		if ($pProject->GetKind() == KIND_CONSOLE_APP)
		{
			
			//PBXProject Project object
			$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXProject Project object")]["attributes"]["TargetAttributes"][ProjetGen_Xcode_Project_GetKey($pProject, "PBXNativeTarget " . $pProject->GetName())]["DevelopmentTeam"] = "LBTHQW46EF";
			
			
			
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
							"path" => $sDependancy,
							"sourceTree" => "SDKROOT"
						);
					}
					else if ($sExtension == "tbd")
					{
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName)] = array(
							"isa" => "PBXFileReference",
							"lastKnownFileType" => "\"sourcecode.text-based-dylib-definition\"",
							"name" => $sBaseName,
							"path" => $sDependancy,
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
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXGroup mainGroup")]["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $sBaseName);
					
					$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFrameworksBuildPhase Frameworks")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $sBaseName . " in Frameworks");
					
					
				}
			}
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
							case "h":
								$xFileObject["lastKnownFileType"] = "sourcecode.c.h";
								$xFileObject["fileEncoding"] = 4;
								break;
							case "m":
								$xFileObject["lastKnownFileType"] = "sourcecode.c.objc";
								$xFileObject["fileEncoding"] = 4;
								break;
							default:
								$xFileObject["lastKnownFileType"] = "file";
						}
						
						$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $xFile["sName"])] = $xFileObject;
						$xFolderObject["children"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $xFile["sName"]);
						
						if ($bSource)
						{
							if ($xFile["sExtension"] == "c" || $xFile["sExtension"] == "m")
							{
								//	A110847D1B32274E0064E12E /* Sync_InstanceFinish.c in Sources */ = {isa = PBXBuildFile; fileRef = A11084761B32274E0064E12E /* Sync_InstanceFinish.c */; };
								$xBuildFileObject = array(
									"isa" => "PBXBuildFile",
									"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $xFile["sName"])
								);
								$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $xFile["sName"] . " in Sources")] = $xBuildFileObject;
								
								$xObjectArray[ProjetGen_Xcode_Project_GetKey($pProject, "PBXSourcesBuildPhase Sources")]["files"][] = ProjetGen_Xcode_Project_GetKey($pProject, "PBXBuildFile " . $xFile["sName"] . " in Sources");
							}
						}
						else
						{
							$xBuildFileObject = array(
								"isa" => "PBXBuildFile",
								"fileRef" => ProjetGen_Xcode_Project_GetKey($pProject, "PBXFileReference " . $xFile["sName"])
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
