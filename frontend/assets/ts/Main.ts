import "@jalno/translator";
import {EmailList} from "./classes/EmailList";
import {Senders} from "./classes/Senders";
import {Templates} from "./classes/Templates";

export default class Main {
	public static init() {
		EmailList.initIfNeeded();
		Senders.initIfNeeded();
		Templates.initIfNeeded();
		Main.prepareCKEDITOR();
	}
	private static prepareCKEDITOR() {
		if (window.hasOwnProperty("CKEDITOR")) {
			(window as any).CKEDITOR.config.contentsLangDirection = Translator.isRTL() ? 'rtl' : 'ltr';
			(window as any).CKEDITOR.config.defaultLanguage = Translator.getActiveShortLang();
			(window as any).CKEDITOR.config.language = Translator.getActiveShortLang();
		}
	}
}

$(() => {
	Main.init();
});