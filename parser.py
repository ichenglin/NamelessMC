def remove_esc(inp):
    out = []
    esc = False
    for c in inp:
        if c == '\\' and not esc:
            esc = True
        else:
            esc = False
            out.append(c)
    return ''.join(out)


def is_literal(inp):
    esc = False
    for c in inp:
        if esc:
            esc = False
        elif c == '\\':
            esc = True
        elif c in {'(', ')'}:
            return False
    return True


def parse(inp):
    if is_literal(inp):
        return remove_esc(inp)

    res = ['', '', None]

    def add_to_res(c):
        if res[2] is None:
            res[1] += c
        else:
            res[2] += c

    level = 0
    esc = False
    for c in inp:
        if esc:
            add_to_res('\\' + c)
            esc = False
            continue

        if c == '\\':
            esc = True
            continue

        if c == '(':
            level += 1
        elif c == ')':
            level -= 1

        if c == '(' and level == 1 or \
                c == ')' and level == 0:
            pass
        elif level == 0:
            res[0] += c
        elif c == ',' and level == 1:
            res[2] = ''
        else:
            add_to_res(c)

        print(c, level, res)

    res[1] = parse(res[1])
    if res[2] is not None:
        res[2] = parse(res[2])
    return res


if __name__ == '__main__':
    test_input = 'and(not(eq(key,v\\)al)),ge(key2,val2))'
    query = parse(test_input)
    print(query)
