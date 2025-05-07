import sys
import language_tool_python

def correct_text(text):
    tool = language_tool_python.LanguageTool('en-US')
    matches = tool.check(text)
    corrected_text = language_tool_python.utils.correct(text, matches)
    tool.close()
    return corrected_text

if __name__ == "__main__":
    if len(sys.argv) < 2:
        print("Error: No text provided", file=sys.stderr)
        sys.exit(1)
    
    input_text = sys.argv[1]
    try:
        corrected = correct_text(input_text)
        print(corrected)
    except Exception as e:
        print(f"Error: {str(e)}", file=sys.stderr)
        sys.exit(1)