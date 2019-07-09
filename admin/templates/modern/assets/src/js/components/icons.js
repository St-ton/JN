
import { config, dom, library } from '@fortawesome/fontawesome-svg-core'

/* config */

config.searchPseudoElements = true
config.keepOriginalSource = false
config.autoReplaceSvg = 'nest'
config.observeMutations = true

/* light variant */

import {
	faAngleDoubleLeft as falAngleDoubleLeft,
	faMapMarkerQuestion as falMapMarkerQuestion,
	faEdit as falEdit,
	faSearch as falSearch,
	faAngleDown as falAngleDown,
	faTimes as falTimes,
	faPlus as falPlus,
	faClone as falClone,
	faTrashAlt as falTrashAlt,
	faStar as falStar,
	faMapSigns as falMapSigns,
	faCog as falCog,
	faPlusCircle as falPlusCircle,
	faUsers as falUsers,
	faUserSecret as falUserSecret,
	faCheck as falCheck,
	faExclamationTriangle as falExclamationTriangle,
	faHandHoldingUsd as falHandHoldingUsd,
	faInfoCircle as falInfoCircle,
	faUpload as falUpload,
	faLongArrowRight as falLongArrowRight,
	faCheckSquare as falCheckSquare,
	faSquare as falSquare
} from '@fortawesome/pro-light-svg-icons'

/* solid variant */

import {
	faStar as fasStar,
	faEdit as fasEdit,
	faBell as fasBell,
	faCircle as fasCircle,
	faUser as fasUser,
	faPlusCircle as fasPlusCircle,
	faCog as fasCog,
	faHandHoldingUsd as fasHandHoldingUsd,
	faClone as fasClone,
	faTrashAlt as fasTrashAlt,
	faTimes as fasTimes,
	faAngleDown as fasAngleDown,
	faInfoCircle as fasInfoCircle,
	faLongArrowRight as fasLongArrowRight
} from '@fortawesome/pro-solid-svg-icons'


library.add(
	falAngleDoubleLeft, falMapMarkerQuestion, fasBell, falEdit,
	falSearch, falAngleDown, fasStar, fasCircle, fasUser, falTimes,
	falPlus, falClone, falTrashAlt, falStar, falMapSigns, falCog,
	falPlusCircle, falUsers, falUserSecret, fasPlusCircle, fasCog,
	falCheck, falExclamationTriangle, falHandHoldingUsd, fasEdit,
	fasHandHoldingUsd, falClone, fasClone, fasTrashAlt, fasTimes,
	fasAngleDown, fasInfoCircle, falInfoCircle, falUpload, falLongArrowRight,
	fasLongArrowRight, falCheckSquare, falSquare
)

dom.watch()
